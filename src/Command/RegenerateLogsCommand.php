<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Album;
use App\Entity\ChoiceList;
use App\Entity\Collection;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Log;
use App\Entity\Photo;
use App\Entity\Tag;
use App\Entity\TagCategory;
use App\Entity\Template;
use App\Entity\Wish;
use App\Entity\Wishlist;
use App\Enum\LogTypeEnum;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:regenerate-logs',
    description: 'Regenerate missing create logs',
)]
class RegenerateLogsCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counter = 0;
        $classes = [
            Collection::class, Item::class,
            Wishlist::class, Wish::class,
            Album::class, Photo::class,
            Tag::class, TagCategory::class,
            Template::class, ChoiceList::class,
            Inventory::class,
        ];

        foreach ($classes as $class) {
            $output->writeln("Checking logs for {$class}...");
            $results = $this->managerRegistry
                ->getRepository($class)
                ->createQueryBuilder('o')
                ->getQuery()
                ->toIterable()
            ;
            $count = $this->managerRegistry->getRepository($class)->createQueryBuilder('o')->select('COUNT (o.id)')->getQuery()->getSingleScalarResult();
            if ($count === 0) {
                continue;
            }

            $progressBar = new ProgressBar($output, $count);
            foreach ($results as $result) {
                $log = $this->managerRegistry->getRepository(Log::class)->findOneBy([
                    'type' => LogTypeEnum::TYPE_CREATE,
                    'objectId' => $result->getId(),
                ]);

                if (!$log instanceof Log) {
                    $log = (new Log())
                        ->setType(LogTypeEnum::TYPE_CREATE)
                        ->setLoggedAt($result->getCreatedAt())
                        ->setObjectClass($class)
                        ->setObjectId($result->getId())
                        ->setObjectLabel($result->__toString())
                        ->setOwner($result->getOwner())
                    ;
                    $this->managerRegistry->getManager()->persist($log);
                    ++$counter;
                }

                $progressBar->advance();
            }

            $this->managerRegistry->getManager()->flush();
            $this->managerRegistry->getManager()->clear();
            $output->writeln('');
        }

        $output->writeln($counter . ' logs generated.');

        $output->writeln("Updating 'delete' logs...");

        $results = $this->managerRegistry->getManager()->createQueryBuilder()
            ->select('l.objectId')
            ->distinct()
            ->from(Log::class, 'l')
            ->where('l.type = ?1')
            ->setParameter(1, LogTypeEnum::TYPE_DELETE)
            ->getQuery()
            ->execute()
        ;

        $ids = array_map(static function ($result) {
            return $result['objectId'];
        }, $results);

        $this->managerRegistry->getManager()->createQueryBuilder()
            ->update(Log::class, 'l')
            ->set('l.objectDeleted', '?1')
            ->where('l.objectId IN (?2)')
            ->setParameter(1, true)
            ->setParameter(2, $ids)
            ->getQuery()
            ->execute()
        ;

        $output->writeln('Done!');

        return Command::SUCCESS;
    }
}
