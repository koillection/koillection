<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Album;
use App\Entity\Collection;
use App\Entity\Wishlist;
use App\Enum\VisibilityEnum;
use App\Repository\AlbumRepository;
use App\Repository\CollectionRepository;
use App\Repository\DatumRepository;
use App\Repository\ItemRepository;
use App\Repository\PhotoRepository;
use App\Repository\WishlistRepository;
use App\Repository\WishRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

class CachedValuesCalculator
{
    public function __construct(
        private readonly CollectionRepository $collectionRepository,
        private readonly ItemRepository $itemRepository,
        private readonly DatumRepository $datumRepository,
        private readonly WishlistRepository $wishlistRepository,
        private readonly WishRepository $wishRepository,
        private readonly AlbumRepository $albumRepository,
        private readonly PhotoRepository $photoRepository,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function refreshAllCaches(OutputInterface $output = null): void
    {
        $output?->writeln('Refreshing cached values for collections...');
        foreach ($this->collectionRepository->findBy(['parent' => null]) as $rootCollection) {
            $this->computeForCollection($rootCollection);
        }

        $output?->writeln('Refreshing cached values for wishlists...');
        foreach ($this->wishlistRepository->findBy(['parent' => null]) as $rootWishlist) {
            $this->computeForWishlist($rootWishlist);
        }

        $output?->writeln('Refreshing cached values for albums...');
        foreach ($this->albumRepository->findBy(['parent' => null]) as $rootAlbum) {
            $this->computeForAlbum($rootAlbum);
        }

        $this->managerRegistry->getManager()->flush();
    }

    public function computeForCollection(Collection $collection): array
    {
        $values = [
            'counters' => [
                'publicCounters' => [
                    'children' => $this->collectionRepository->count(['parent' => $collection->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PUBLIC]),
                    'items' => $this->itemRepository->count(['collection' => $collection->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PUBLIC]),
                ],
                'internalCounters' => [
                    'children' => $this->collectionRepository->count(['parent' => $collection->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_INTERNAL]),
                    'items' => $this->itemRepository->count(['collection' => $collection->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_INTERNAL]),
                ],
                'privateCounters' => [
                    'children' => $this->collectionRepository->count(['parent' => $collection->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PRIVATE]),
                    'items' => $this->itemRepository->count(['collection' => $collection->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PRIVATE]),
                ]
            ],
            'prices' =>[
                'publicPrices' => $this->datumRepository->computePricesForCollection($collection, VisibilityEnum::VISIBILITY_PUBLIC),
                'internalPrices' => $this->datumRepository->computePricesForCollection($collection, VisibilityEnum::VISIBILITY_INTERNAL),
                'privatePrices' => $this->datumRepository->computePricesForCollection($collection, VisibilityEnum::VISIBILITY_PRIVATE),
            ],
        ];


        foreach ($collection->getChildren() as $child) {
            $nestedCounters = $this->computeForCollection($child);

            $values['counters']['publicCounters']['children'] += $nestedCounters['counters']['publicCounters']['children'];
            $values['counters']['publicCounters']['items'] += $nestedCounters['counters']['publicCounters']['items'];

            $values['counters']['internalCounters']['children'] += $nestedCounters['counters']['internalCounters']['children'];
            $values['counters']['internalCounters']['items'] += $nestedCounters['counters']['internalCounters']['items'];

            $values['counters']['privateCounters']['children'] += $nestedCounters['counters']['privateCounters']['children'];
            $values['counters']['privateCounters']['items'] += $nestedCounters['counters']['privateCounters']['items'];


            foreach ($nestedCounters['prices']['publicPrices'] as $label => $currencies) {
                foreach ($currencies as $currency => $value) {
                    if (isset($values['prices']['publicPrices'][$label][$currency])) {
                        $values['prices']['publicPrices'][$label][$currency] += $value;
                    } else {
                        $values['prices']['publicPrices'][$label][$currency] = $value;
                    }
                }
            }

            foreach ($nestedCounters['prices']['privatePrices'] as $label => $currencies) {
                foreach ($currencies as $currency => $value) {
                    if (isset($values['prices']['privatePrices'][$label][$currency])) {
                        $values['prices']['privatePrices'][$label][$currency] += $value;
                    } else {
                        $values['prices']['privatePrices'][$label][$currency] = $value;
                    }
                }
            }

            foreach ($nestedCounters['prices']['internalPrices'] as $label => $currencies) {
                foreach ($currencies as $currency => $value) {
                    if (isset($values['prices']['internalPrices'][$label][$currency])) {
                        $values['prices']['internalPrices'][$label][$currency] += $value;
                    } else {
                        $values['prices']['internalPrices'][$label][$currency] = $value;
                    }
                }
            }
        }

        $collection->setCachedValues($values);

        return $values;
    }

    public function computeForWishlist(Wishlist $wishlist): array
    {
        $values = [
            'counters' => [
                'publicCounters' => [
                    'children' => $this->wishlistRepository->count(['parent' => $wishlist->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PUBLIC]),
                    'wishes' => $this->wishRepository->count(['wishlist' => $wishlist->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PUBLIC]),
                ],
                'internalCounters' => [
                    'children' => $this->wishlistRepository->count(['parent' => $wishlist->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_INTERNAL]),
                    'wishes' => $this->wishRepository->count(['wishlist' => $wishlist->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_INTERNAL]),
                ],
                'privateCounters' => [
                    'children' => $this->wishlistRepository->count(['parent' => $wishlist->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PRIVATE]),
                    'wishes' => $this->wishRepository->count(['wishlist' => $wishlist->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PRIVATE]),
                ]
            ],
        ];

        foreach ($wishlist->getChildren() as $child) {
            $nestedCounters = $this->computeForWishlist($child);
            $values['counters']['publicCounters']['children'] += $nestedCounters['counters']['publicCounters']['children'];
            $values['counters']['publicCounters']['wishes'] += $nestedCounters['counters']['publicCounters']['wishes'];

            $values['counters']['internalCounters']['children'] += $nestedCounters['counters']['internalCounters']['children'];
            $values['counters']['internalCounters']['wishes'] += $nestedCounters['counters']['internalCounters']['wishes'];

            $values['counters']['privateCounters']['children'] += $nestedCounters['counters']['privateCounters']['children'];
            $values['counters']['privateCounters']['wishes'] += $nestedCounters['counters']['privateCounters']['wishes'];
        }

        $wishlist->setCachedValues($values);

        return $values;
    }

    public function computeForAlbum(Album $album): array
    {
        $values = [
            'counters' => [
                'publicCounters' => [
                    'children' => $this->albumRepository->count(['parent' => $album->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PUBLIC]),
                    'photos' => $this->photoRepository->count(['album' => $album->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PUBLIC]),
                ],
                'internalCounters' => [
                    'children' => $this->albumRepository->count(['parent' => $album->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_INTERNAL]),
                    'photos' => $this->photoRepository->count(['album' => $album->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_INTERNAL]),
                ],
                'privateCounters' => [
                    'children' => $this->albumRepository->count(['parent' => $album->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PRIVATE]),
                    'photos' => $this->photoRepository->count(['album' => $album->getId(), 'finalVisibility' => VisibilityEnum::VISIBILITY_PRIVATE]),
                ]
            ],
        ];

        foreach ($album->getChildren() as $child) {
            $nestedCounters = $this->computeForAlbum($child);
            $values['counters']['publicCounters']['children'] += $nestedCounters['counters']['publicCounters']['children'];
            $values['counters']['publicCounters']['photos'] += $nestedCounters['counters']['publicCounters']['photos'];

            $values['counters']['internalCounters']['children'] += $nestedCounters['counters']['internalCounters']['children'];
            $values['counters']['internalCounters']['photos'] += $nestedCounters['counters']['internalCounters']['photos'];

            $values['counters']['privateCounters']['children'] += $nestedCounters['counters']['privateCounters']['children'];
            $values['counters']['privateCounters']['photos'] += $nestedCounters['counters']['privateCounters']['photos'];
        }

        $album->setCachedValues($values);

        return $values;
    }
}
