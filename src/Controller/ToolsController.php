<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\CsvResponse;
use App\Http\FileResponse;
use App\Repository\CollectionRepository;
use App\Repository\InventoryRepository;
use App\Service\DatabaseDumper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use ZipStream\ZipStream;

class ToolsController extends AbstractController
{
    #[Route(path: '/tools', name: 'app_tools_index', methods: ['GET'])]
    public function index(InventoryRepository $inventoryRepository): Response
    {
        return $this->render('App/Tools/index.html.twig', [
            'inventories' => $inventoryRepository->findAll(),
        ]);
    }

    #[Route(path: '/tools/export/printable-list', name: 'app_tools_export_printable_list', methods: ['GET'])]
    public function exportPrintableList(CollectionRepository $collectionRepository): Response
    {
        $collections = $collectionRepository->findAllWithItems();

        return $this->render('App/Tools/printable_list.html.twig', [
            'collections' => $collections,
            'user' => $this->getUser(),
        ]);
    }

    #[Route(path: '/tools/export/csv', name: 'app_tools_export_csv', methods: ['GET'])]
    public function exportCsv(CollectionRepository $collectionRepository): CsvResponse
    {
        $collections = $collectionRepository->findAllWithItems();

        $rows = [];
        foreach ($collections as $collection) {
            foreach ($collection->getItems() as $item) {
                $rows[] = [$item->getId(), $item->getName(), $collection->getTitle()];
            }
        }

        return new CsvResponse($rows, (new \DateTimeImmutable())->format('YmdHis') . '-koillection-export.csv');
    }

    #[Route(path: '/tools/export/images', name: 'app_tools_export_images', methods: ['GET'])]
    public function exportImages(#[Autowire('%kernel.project_dir%/public/uploads')] string $uploadsPath): StreamedResponse
    {
        return new StreamedResponse(function () use ($uploadsPath): void {
            $zipFilename = (new \DateTimeImmutable())->format('YmdHis') . '-koillection-images.zip';
            $zip = new ZipStream(outputName: $zipFilename, sendHttpHeaders: true, defaultEnableZeroHeader: true, flushOutput: true);

            $path = $uploadsPath . '/' . $this->getUser()->getId();
            if (!is_dir($path)) {
                $zip->finish();
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $zip->addFileFromStream($file->getFilename(), fopen($file->getRealPath(), 'r'));
                }
            }

            $zip->finish();
        });
    }
}
