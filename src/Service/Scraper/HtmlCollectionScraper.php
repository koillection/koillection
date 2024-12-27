<?php

declare(strict_types=1);

namespace App\Service\Scraper;

use App\Enum\DatumTypeEnum;
use App\Enum\ScraperTypeEnum;
use App\Model\ScrapingCollection;

class HtmlCollectionScraper extends HtmlScraper
{
    public function scrap(ScrapingCollection $scraping): array
    {
        $crawler = $this->getCrawler($scraping);
        $scraper = $scraping->getScraper();

        $image = $scraping->getScrapImage() ? $this->extract($scraper->getImagePath(), DatumTypeEnum::TYPE_TEXT, $crawler, $scraping) : null;
        $image = $this->guessHost($image, $scraping);

        return [
            'name' => $scraping->getScrapName() ? $this->extract($scraper->getNamePath(), DatumTypeEnum::TYPE_TEXT, $crawler, $scraping) : null,
            'base64Image' => 'data:image/png;base64,' . base64_encode(file_get_contents($image)),
            'data' => $this->scrapData($scraping, $crawler, ScraperTypeEnum::TYPE_COLLECTION),
            'scrapedUrl' => $scraping->getUrl()
        ];
    }
}
