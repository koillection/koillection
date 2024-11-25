<?php

declare(strict_types=1);

namespace Api\Controller;

use App\Enum\ConfigurationEnum;
use App\Repository\AlbumRepository;
use App\Repository\CollectionRepository;
use App\Repository\ConfigurationRepository;
use App\Repository\DatumRepository;
use App\Repository\ItemRepository;
use App\Repository\PhotoRepository;
use App\Repository\ScraperRepository;
use App\Repository\TagCategoryRepository;
use App\Repository\TagRepository;
use App\Repository\TemplateRepository;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use App\Repository\WishRepository;
use App\Service\DiskUsageCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MetricsController extends AbstractController
{
    private array $lines = [];

    #[Route(
        path: '/api/metrics',
        name: 'api_metrics',
        methods: ['GET']
    )]
    public function __invoke(
        ConfigurationRepository $configurationRepository,
        UserRepository $userRepository,
        CollectionRepository $collectionRepository,
        ItemRepository $itemRepository,
        DatumRepository $datumRepository,
        TagRepository $tagRepository,
        TagCategoryRepository $tagCategoryRepository,
        WishlistRepository $wishlistRepository,
        WishRepository $wishRepository,
        AlbumRepository $albumRepository,
        PhotoRepository $photoRepository,
        TemplateRepository $templateRepository,
        ScraperRepository $scraperRepository,
        DiskUsageCalculator $diskUsageCalculator
    ): Response
    {
        if ($configurationRepository->findOneBy(['label' => ConfigurationEnum::ENABLE_METRICS])->getValue() !== '1') {
            throw new AccessDeniedHttpException();
        }

        $users = $userRepository->findAll();

        $this->addCounter('user', [['label' => '', 'value' => $userRepository->count()]], 'number of registered users');

        // Create global counters
        $collectionValues[] = ['label' => null, 'value' => $collectionRepository->count()];
        $itemValues[] = ['label' => null, 'value' => $itemRepository->count()];
        $datumValues[] = ['label' => null, 'value' => $datumRepository->count()];
        $tagValues[] = ['label' => null, 'value' => $tagRepository->count()];
        $tagCategoryValues[] = ['label' => null, 'value' => $tagCategoryRepository->count()];
        $wishlistValues[] = ['label' => null, 'value' => $wishlistRepository->count()];
        $wishValues[] = ['label' => null, 'value' => $wishRepository->count()];
        $albumValues[] = ['label' => null, 'value' => $albumRepository->count()];
        $photoValues[] = ['label' => null, 'value' => $photoRepository->count()];
        $templateValues[] = ['label' => null, 'value' => $templateRepository->count()];
        $scraperValues[] = ['label' => null, 'value' => $scraperRepository->count()];
        $diskUsedValues[] = ['label' => null, 'value' => $diskUsageCalculator->getSpaceUsedByUsers()];
        $diskAvailableValues = [];

        // Create counters per user
        foreach ($users as $user) {
            $label = "{user=\"{$user->getUsername()}\"}";
            $collectionValues[] = ['label' => $label, 'value' => $collectionRepository->count(['owner' => $user])];
            $itemValues[] = ['label' => $label, 'value' => $itemRepository->count(['owner' => $user])];
            $datumValues[] = ['label' => $label, 'value' => $datumRepository->count(['owner' => $user])];
            $tagValues[] = ['label' => $label, 'value' => $tagRepository->count(['owner' => $user])];
            $tagCategoryValues[] = ['label' => $label, 'value' => $tagCategoryRepository->count(['owner' => $user])];
            $wishlistValues[] = ['label' => $label, 'value' => $wishlistRepository->count(['owner' => $user])];
            $wishValues[] = ['label' => $label, 'value' => $wishRepository->count(['owner' => $user])];
            $albumValues[] = ['label' => $label, 'value' => $albumRepository->count(['owner' => $user])];
            $photoValues[] = ['label' => $label, 'value' => $photoRepository->count(['owner' => $user])];
            $templateValues[] = ['label' => $label, 'value' => $templateRepository->count(['owner' => $user])];
            $scraperValues[] = ['label' => $label, 'value' => $scraperRepository->count(['owner' => $user])];
            $diskUsedValues[] = ['label' => $label, 'value' => $diskUsageCalculator->getSpaceUsedByUser($user)];
            $diskAvailableValues[] = ['label' => $label, 'value' => $user->getDiskSpaceAllowed()];
        }

        // Fill metrics
        $this->addCounter('collection', $collectionValues, 'number of created collections');
        $this->addCounter('item', $itemValues, 'number of created items');
        $this->addCounter('datum', $datumValues, 'number of created data');
        $this->addCounter('tag', $tagValues, 'number of created tags');
        $this->addCounter('tag_category', $tagCategoryValues, 'number of created tag categories');
        $this->addCounter('wishlist', $wishlistValues, 'number of created wishlists');
        $this->addCounter('wish', $wishValues, 'number of created wishes');
        $this->addCounter('album', $albumValues, 'number of created albums');
        $this->addCounter('photo', $photoValues, 'number of created photos');
        $this->addCounter('template', $templateValues, 'number of created templates');
        $this->addCounter('scraper', $scraperValues, 'number of created scrapers');
        $this->addCounter('used_disk_space_bytes', $diskUsedValues, 'used disk space by uploads (images, videos, files)', 'bytes');
        $this->addCounter('available_disk_space_bytes', $diskAvailableValues, 'available disk space for uploads (images, videos, files)', 'bytes');

        $metrics = implode(PHP_EOL, $this->lines);
        $response = new Response();
        $response->setContent($metrics);

        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

    public function addCounter(string $name, array $values, string $help, ?string $unit = null): void
    {
        //$name = "koillection_{$name}";

        $this->lines[] = "# HELP {$name} {$help}";
        if ($unit !== null) {
            $this->lines[] = "# UNIT {$name} {$unit}";
        }

        $this->lines[] = "# TYPE {$name} counter";

        foreach ($values as $value) {
            $this->lines[] = "{$name}_total{$value['label']} {$value['value']}";
        }
    }
}
