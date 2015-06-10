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
        $container = $lorry->getContainer();
        try {
            $container->get('Lorry\TemplateEngineInterface')->clearCacheFiles();
        }
        catch(\UnexpectedValueException $ex) {
            $output->write('<warning>Non-critical error clearing Twig cache: '.$ex->getMessage().'</warning>');
        }
        $container->get('Doctrine\Common\Cache\Cache')->flushAll();
        $output->writeln('Cleared cache');
    }
}
