<?php

declare(strict_types=1);

namespace App\Service\Scraper;

use App\Entity\Datum;
use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use App\Model\ScrapingCollection;
use App\Model\ScrapingItem;
use App\Model\ScrapingWish;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

abstract class HtmlScraper
{
    public function __construct(
        protected HttpClientInterface $client,
        protected Environment $twig
    ) {
    }

    protected function getCrawler(ScrapingItem|ScrapingCollection|ScrapingWish $scraping): Crawler
    {
        if ($scraping->getFile() instanceof UploadedFile) {
            $content = $scraping->getFile()->getContent();
        } else {
            $response = $this->client->request(
                'GET',
                $scraping->getUrl(),
                ['timeout' => 2.5, 'verify_peer' => false, 'verify_host' => false]
            );

            if (200 !== $response->getStatusCode()) {
                throw new \Exception('Api error: ' . $response->getStatusCode() . ' - ' . $response->getContent());
            }

            $content = $response->getContent();
        }

        return new Crawler($content);
    }

    protected function extract(?string $template, string $type, Crawler $crawler, $scraping): ?string
    {
        if (!$template) {
            return '';
        }

        $values = [];
        preg_match_all('/#(.*?)#/', $template, $matches);

        foreach ($matches[1] as $xPath) {
            $results = $crawler->evaluate($xPath);

            if ($results instanceof Crawler) {
                $results = $results->each(static function (Crawler $node): string {
                    return $node->text();
                });
            }

            foreach ($results as $key => $result) {
                if (isset($values[$key])) {
                    $values[$key] = str_replace("#{$xPath}#", $result, $values[$key]);
                } else {
                    $values[$key] = str_replace("#{$xPath}#", $result, $template);
                }
            }

            // Remove xPath from result in case nothing was found
            foreach ($values as &$value) {
                $value = str_replace("#{$xPath}#", '', $value);
            }
        }

        return $this->formatValues($values, $type, $scraping);
    }

    protected function scrapData(ScrapingItem|ScrapingCollection|ScrapingWish $scraping, Crawler $crawler, string $entityType): array
    {
        $data = [];

        foreach ($scraping->getDataToScrap() as $key => $dataToScrap) {
            $value = $this->extract($dataToScrap->getPath(), $dataToScrap->getType(), $crawler, $scraping);

            $datum = (new Datum())
                ->setValue($value)
                ->setLabel($dataToScrap->getName())
                ->setType($dataToScrap->getType())
                ->setPosition((int) $key)
            ;

            $data[] = [
                $dataToScrap->getType(),
                $dataToScrap->getName(),
                $this->twig->render('App/Datum/_datum.html.twig', [
                    'entity' => $entityType,
                    'iteration' => '__placeholder__',
                    'type' => $dataToScrap->getType(),
                    'datum' => $datum,
                    'label' => $datum->getLabel(),
                    'choiceList' => $datum->getChoiceList(),
                    'visibility' => $datum->getVisibility()
                ])
            ];
        }

        return $data;
    }

    protected function formatValues(?array $values, string $type, $scraping): ?string
    {
        if ($values === null || $values === []) {
            return null;
        }

        if ($type === DatumTypeEnum::TYPE_TEXT) {
            return implode(', ', $values);
        }

        if ($type === DatumTypeEnum::TYPE_LIST) {
            return json_encode($values);
        }

        if ($type === DatumTypeEnum::TYPE_TEXTAREA) {
            return $values[0];
        }

        if ($type === DatumTypeEnum::TYPE_COUNTRY) {
            $value = array_shift($values);

            // Try to match alpha2 code
            if (\strlen($value) === 2 && Countries::exists(strtoupper($value))) {
                return strtoupper($value);
            }

            // Try to match alpha3 code
            if (\strlen($value) === 3 && Countries::alpha3CodeExists(strtoupper($value))) {
                return strtoupper($value);
            }

            // Else try to match the country name
            return array_flip(Countries::getNames())[$value] ?? null;
        }

        if ($type === DatumTypeEnum::TYPE_IMAGE) {
            return $this->guessHost($values[0], $scraping);
        }

        return null;
    }

    protected function guessHost(?string $url, ScrapingItem|ScrapingCollection|ScrapingWish $scraping): ?string
    {
        if ($url === null || $scraping->getUrl() === null) {
            return null;
        }

        $urlElements = parse_url($url);
        if (!isset($urlElements['host'])) {
            $scrapingUrlElements = parse_url($scraping->getUrl());
            $url = $scrapingUrlElements['scheme'] . '://' . $scrapingUrlElements['host'] . $urlElements['path'];
        }

        return $url;
    }
}
