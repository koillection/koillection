<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Datum;
use App\Entity\Item;
use App\Entity\Photo;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wish;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsCommand(
    name: 'app:regenerate-thumbnails',
    description: 'Regenerate thumbnails',
)]
class RegenerateThumbnailsCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TokenStorageInterface $tokenStorage,
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

        $counter = 0;
        $classes = [Item::class, Datum::class, Wish::class, Photo::class, Tag::class];
        $users = $this->managerRegistry->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            $objects = [];
            $output->writeln("Regenerating thumbnails for user {$user}...");

            // Login user, needed for uploads
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            foreach ($classes as $class) {
                $result = $this->managerRegistry->getRepository($class)->createQueryBuilder('o')
                    ->where('o.image IS NOT NULL')
                    ->andWhere('o.owner = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getResult();

                $objects = array_merge($objects, $result);
            }

            if ($objects === []) {
                continue;
            }

            $progressBar = new ProgressBar($output, \count($objects));
            foreach ($objects as $object) {
                $imagePath = $this->publicPath . '/' . $object->getImage();

                if (is_file($imagePath)) {
                    $filename = basename($imagePath);
                    $mime = mime_content_type($imagePath);
                    $file = new UploadedFile($imagePath, $filename, $mime, null, true);

                    if ($object instanceof Datum) {
                        $object->setFileImage($file);
                    } else {
                        $object->setFile($file);
                    }

                    ++$counter;
                }

                if ($counter % 100 !== 0) {
                    $this->managerRegistry->getManager()->flush();
                }

                $progressBar->advance();
            }

            $this->managerRegistry->getManager()->flush();
            $output->writeln('');
        }

        $output->writeln("{$counter} thumbnails regenerated.");
        $output->writeln('Done!');

        return Command::SUCCESS;
    }
}
