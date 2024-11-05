<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Enum\DatumTypeEnum;
use App\Http\FileResponse;
use App\Repository\AlbumRepository;
use App\Repository\CollectionRepository;
use App\Repository\DatumRepository;
use App\Repository\ItemRepository;
use App\Repository\PhotoRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use App\Repository\WishRepository;
use App\Service\CachedValuesCalculator;
use App\Service\DatabaseDumper;
use App\Service\LatestReleaseChecker;
use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZipStream\ZipStream;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route(path: '/admin', name: 'app_admin_index', methods: ['GET'])]
    public function index(
        LatestReleaseChecker $latestVersionChecker,
        UserRepository $userRepository,
        CollectionRepository $collectionRepository,
        ItemRepository $itemRepository,
        TagRepository $tagRepository,
        WishlistRepository $wishlistRepository,
        WishRepository $wishRepository,
        AlbumRepository $albumRepository,
        PhotoRepository $photoRepository,
        DatumRepository $datumRepository
    ): Response {
        return $this->render('App/Admin/Admin/index.html.twig', [
            'freeSpace' => disk_free_space('/'),
            'totalSpace' => disk_total_space('/'),
            'counters' => [
                'users' => $userRepository->count([]),
                'collections' => $collectionRepository->count([]),
                'items' => $itemRepository->count([]),
                'tags' => $tagRepository->count([]),
                'wishlists' => $wishlistRepository->count([]),
                'wishes' => $wishRepository->count([]),
                'albums' => $albumRepository->count([]),
                'photos' => $photoRepository->count([]),
                'signs' => $datumRepository->count(['type' => DatumTypeEnum::TYPE_SIGN]),
            ],
            'currentRelease' => $latestVersionChecker->getCurrentRelease(),
            'latestRelease' => $latestVersionChecker->getLatestRelease(),
            'requiredPhpVersionForLatestRelease' => $latestVersionChecker->getRequiredPhpVersionForLatestRelease(),
            'isRequiredPhpVersionForLatestReleaseOk' => $latestVersionChecker->isRequiredPhpVersionForLatestReleaseOk(),
            'symfonyVersion' => Kernel::VERSION,
            'apiPlatformVersion' => str_replace('v', '', InstalledVersions::getPrettyVersion('api-platform/core')),
            'phpVersion' => phpversion(),
            'isOpcacheAvailable' => \function_exists('opcache_get_status') && opcache_get_status() && opcache_get_status()['opcache_enabled'],
            'frankenPhpVersion' => \extension_loaded('frankenphp') ? str_replace('v', '', phpversion('frankenphp')) : null
        ]);
    }

    #[Route(path: '/admin/export/images', name: 'app_admin_export_images', methods: ['GET'])]
    public function exportImages(
        UserRepository $userRepository,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $uploadsPath
    ): StreamedResponse {
        $users = $userRepository->findAll();

        return new StreamedResponse(static function () use ($users, $uploadsPath): void {
            $zipFilename = (new \DateTimeImmutable())->format('YmdHis') . '-koillection-images.zip';
            $zip = new ZipStream(outputName: $zipFilename, sendHttpHeaders: true, defaultEnableZeroHeader: true, flushOutput: true);

            foreach ($users as $user) {
                $path = $uploadsPath . '/' . $user->getId();

                if (!is_dir($path)) {
                    continue;
                }

                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $zip->addFileFromStream($user->getId() . '/' . $file->getFilename(), fopen($file->getRealPath(), 'r'));
                    }
                }
            }

            $zip->finish();
        });
    }

    #[Route(path: '/admin/refresh-caches', name: 'app_admin_refresh_caches', methods: ['GET'])]
    public function refreshCaches(CachedValuesCalculator $cachedValuesCalculator, TranslatorInterface $translator): Response
    {
        $cachedValuesCalculator->refreshAllCaches();
        $this->addFlash('notice', $translator->trans('message.caches_refreshed'));

        return $this->redirectToRoute('app_admin_index');
    }
}
