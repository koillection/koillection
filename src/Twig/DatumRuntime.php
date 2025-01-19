<?php

declare(strict_types=1);

namespace App\Twig;

use App\Repository\DatumRepository;
use Twig\Extension\RuntimeExtensionInterface;

class DatumRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly DatumRepository $datumRepository)
    {
    }

    public function getListValuesFromDatumLabelAndType(?string $label, string $type): array
    {
        $elements = [];

        $data = $this->datumRepository->findBy(['label' => $label, 'type' => $type]);

        foreach ($data as $datum) {
            $values = json_decode($datum->getValue(), true);
            foreach ($values as $value) {
                if (!in_array($value, $elements)) {
                    $elements[] = $value;
                }
            }
        }

        return $elements;
    }
}
