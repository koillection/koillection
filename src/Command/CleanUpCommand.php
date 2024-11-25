<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:clean-up',
    description: 'Delete unused images',
)]
class CleanUpCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        #[Autowire('%kernel.project_dir%/public')] private readonly string $publicPath
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This action can be dangerous, please do a backup of both your database and /uploads folder. Are you sure you want to continue ? (y/N)', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $output->writeln('Getting all files paths from database...');
        $sql = '
            SELECT image AS image FROM koi_collection WHERE image IS NOT NULL UNION

            SELECT image AS image FROM koi_album WHERE image IS NOT NULL UNION
            
            SELECT image AS image FROM koi_wishlist WHERE image IS NOT NULL UNION
            
            SELECT avatar AS image FROM koi_user WHERE avatar IS NOT NULL UNION
            
            SELECT image AS image FROM koi_tag WHERE image IS NOT NULL UNION
            SELECT image_small_thumbnail AS image FROM koi_tag WHERE image_small_thumbnail IS NOT NULL UNION
            
            SELECT image AS image FROM koi_photo WHERE image IS NOT NULL UNION
            SELECT image_small_thumbnail AS image FROM koi_photo WHERE image_small_thumbnail IS NOT NULL UNION
            
            SELECT image AS image FROM koi_item WHERE image IS NOT NULL UNION
            SELECT image_small_thumbnail AS image FROM koi_item WHERE image_small_thumbnail IS NOT NULL UNION
            SELECT image_large_thumbnail AS image FROM koi_item WHERE image_large_thumbnail IS NOT NULL UNION
            
            SELECT image AS image FROM koi_datum WHERE image IS NOT NULL UNION
            SELECT image_small_thumbnail AS image FROM koi_datum WHERE image_small_thumbnail IS NOT NULL UNION
            SELECT image_large_thumbnail AS image FROM koi_datum WHERE image_large_thumbnail IS NOT NULL UNION
            SELECT file AS image FROM koi_datum WHERE file IS NOT NULL UNION
            
            SELECT image AS image FROM koi_wish WHERE image IS NOT NULL UNION
            SELECT image_small_thumbnail AS image FROM koi_wish WHERE image_small_thumbnail IS NOT NULL;
        ';

        $stmt = $this->managerRegistry->getManager()->getConnection()->prepare($sql);
        $result = $stmt->executeQuery();

        $dbPaths = array_map(static function ($row) {
            return $row['image'];
        }, $result->fetchAllAssociative());

        $output->writeln('Getting all files paths from /uploads...');
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->publicPath . '/uploads'));
        $diskPaths = [];
        foreach ($rii as $file) {
            if (!$file->isDir() && '.gitkeep' !== $file->getFileName()) {
                $diskPaths[] = str_replace($this->publicPath . '/', '', $file->getPathname());
            }
        }

        // Compute the diff and delete the diff
        $output->writeln('Computing diff and delete unused files...');
        $diff = array_diff($diskPaths, $dbPaths);

        if ($diff !== []) {
            $progressBar = new ProgressBar($output, \count($diff));
            foreach ($diff as $path) {
                $progressBar->advance();
                if (file_exists($this->publicPath . '/' . $path)) {
                    unlink($this->publicPath . '/' . $path);
                }
            }

            $output->writeln('');
        }

        $output->writeln(\count($diff) . ' files deleted.');
        $output->writeln('Done!');

        return Command::SUCCESS;
    }
}
