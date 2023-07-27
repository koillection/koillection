<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Scraper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

class ScraperImporter
{
    #[Assert\File(mimeTypes: ['application/json'])]
    #[Assert\NotBlank]
    private ?File $file = null;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): ScraperImporter
    {
        $this->file = $file;

        return $this;
    }

    public function toScrapper(): Scraper
    {
        $scraper = new Scraper();
        $data = json_decode($this->file->getContent(), true);

        $scraper->setName($data['name'] ?? null);
        $scraper->setNamePath($data['namePath'] ?? null);
        $scraper->setImagePath($data['imagePath'] ?? null);
        $scraper->setDataPaths($data['dataPaths'] ?? []);

        return $scraper;
    }
}
