<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CachedValuesCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:refresh-cached-values',
    description: 'Refresh cached values such has counters and prices',
)]
class RefreshCachedValuesCommand extends Command
{
    public function __construct(
        private readonly CachedValuesCalculator $cachedValuesCalculator
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cachedValuesCalculator->refreshAllCaches($output);
        $output->writeln('Done!');

        return Command::SUCCESS;
    }
}
