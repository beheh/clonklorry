<?php

namespace Lorry\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lorry\Environment;

class CacheClearer extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear compiled template cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$lorry = new Environment();
		$lorry->setup();
		$lorry->getTemplating()->clearCacheFiles();
        $output->writeln('Cleared cache');
    }
}
