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
            $prices = $this->mergeAndAdd($prices, $collection->getCachedValues()['prices']['privatePrices'], true);
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

    private function mergeAndAdd(array $array1, array $array2, $debug = false): array
    {
        // Initialize the result array with the first array
        $result = $array1;

        // Loop through all the keys of the second array
        foreach ($array2 as $key => $value) {
            // If the key exists in both arrays and both values are arrays, recurse
            if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                $result[$key] = $this->mergeAndAdd($result[$key], $value);
            }
            // If both values are integers or floats, add them together
            elseif (isset($result[$key]) && (is_numeric($result[$key]) && is_numeric($value))) {
                $result[$key] += $value;
            }
            // Otherwise, set the value from the second array
            else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
