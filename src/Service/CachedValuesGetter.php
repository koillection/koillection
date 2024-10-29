<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Album;
use App\Entity\Collection;
use App\Entity\User;
use App\Entity\Wishlist;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CachedValuesGetter
{
    public function __construct(
        private Security $security
    ) {
    }

    public function getCachedValues(Collection|Album|Wishlist $entity): array
    {
        return match (true) {
            $entity instanceof Collection => $this->getForCollection($entity),
            $entity instanceof Album => $this->getForAlbum($entity),
            $entity instanceof Wishlist => $this->getForWishlist($entity),
        };
    }

    private function getForCollection(Collection $collection): array {
        $prices = $collection->getCachedValues()['prices']['publicPrices'];
        $counters = $collection->getCachedValues()['counters']['publicCounters'];

        if ($this->security->getUser() instanceof User) {
            $prices = $this->mergeAndAdd($prices, $collection->getCachedValues()['prices']['internalPrices']);
            $counters = $this->mergeAndAdd($counters, $collection->getCachedValues()['counters']['internalCounters']);
        }

        if ($this->security->getUser() === $collection->getOwner()) {
            $prices = $this->mergeAndAdd($prices, $collection->getCachedValues()['prices']['privatePrices']);
            $counters = $this->mergeAndAdd($counters, $collection->getCachedValues()['counters']['privateCounters']);
        }

        return [
            'prices' => $prices,
            'counters' => $counters,
        ];
    }

    private function getForAlbum(Album $album): array {
        $counters = $album->getCachedValues()['counters']['publicCounters'];

        if ($this->security->getUser() instanceof User) {
            $counters = $this->mergeAndAdd($counters, $album->getCachedValues()['counters']['internalCounters']);
        }

        if ($this->security->getUser() === $album->getOwner()) {
            $counters = $this->mergeAndAdd($counters, $album->getCachedValues()['counters']['privateCounters']);
        }

        return [
            'counters' => $counters,
        ];
    }

    private function getForWishlist(Wishlist $wishlist): array {
        $counters = $wishlist->getCachedValues()['counters']['publicCounters'];

        if ($this->security->getUser() instanceof User) {
            $counters = $this->mergeAndAdd($counters, $wishlist->getCachedValues()['counters']['internalCounters']);
        }

        if ($this->security->getUser() === $wishlist->getOwner()) {
            $counters = $this->mergeAndAdd($counters, $wishlist->getCachedValues()['counters']['privateCounters']);
        }

        return [
            'counters' => $counters,
        ];
    }

    private function mergeAndAdd(array $array1, array $array2): array
    {
        $result = [];

        if ($array2 === []) {
            return $array1;
        }

        if ($array1 === []) {
            return $array2;
        }

        foreach (array_keys($array1 + $array2) as $value) {
            $result[$value] = ($array1[$value] ?? 0) + ($array2[$value] ?? 0);
        }

        return $result;
    }
}
