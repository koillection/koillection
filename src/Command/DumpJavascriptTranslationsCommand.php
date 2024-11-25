<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\JavascriptTranslationsDumper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:translations:dump',
    description: 'Dumps javascripts translations',
)]
class DumpJavascriptTranslationsCommand extends Command
{
    public function __construct(
        private readonly JavascriptTranslationsDumper $dumper,
        #[Autowire('%kernel.project_dir%/assets/js')] private readonly string $javascriptsPath
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!is_dir($dir = \dirname($this->javascriptsPath)) && !@mkdir($dir, 0777, true)) {
            throw new \RuntimeException('Unable to create directory ' . $dir);
        }

        $output->writeln("Dumping translations files into {$this->javascriptsPath}...");
        $this->dumper->dump($this->javascriptsPath);
        $output->writeln('Done!');

        return Command::SUCCESS;
    }
}
