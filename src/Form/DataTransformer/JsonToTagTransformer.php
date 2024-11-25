<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;

class JsonToTagTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly TagRepository $tagRepository
    ) {
    }

    #[\Override]
    public function transform($tags): string|bool
    {
        $array = [];
        foreach ($tags as $tag) {
            $array[] = $tag->getLabel();
        }

        return json_encode($array);
    }

    #[\Override]
    public function reverseTransform($json): array
    {
        $tags = [];
        foreach (json_decode($json) as $raw) {
            $label = trim($raw);

            if ('' === $label) {
                continue;
            }

            $tag = $this->tagRepository->findOneBy(['label' => $label]);

            if ($tag === null) {
                $tag = new Tag();
                $tag->setLabel($label);
            }

            if (!\in_array($tag, $tags, false)) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
