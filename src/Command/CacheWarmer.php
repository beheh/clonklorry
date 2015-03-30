<?php

namespace Lorry\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Lorry\Environment;
use Symfony\Component\Console\Helper\ProgressBar;

class CacheWarmer extends Command {

	protected function configure() {
		$this
				->setName('cache:warmup')
				->setDescription('Warm up template cache')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$lorry = new Environment();
		$lorry->setup();
		$templateEngine = $lorry->getContainer()->get('Lorry\TemplateEngineInterface');
		
		$base = $lorry::PROJECT_ROOT.'/app/templates';

		$templateEngine->clearTemplateCache();

		if($output->isDebug()) {
			$output->writeln('Looking for templates in '.$base);
		}

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base), RecursiveIteratorIterator::LEAVES_ONLY);
		$templates = array();
		foreach($iterator as $file) {
			if(!$file->isFile() || $file->getExtension() !== 'twig') {
				continue;
			}
			$templates[] = ltrim(substr($file->getPathname(), strlen($base)), '/');
		}

        $progress = null;
		if($output->isVerbose()) {
			$progress = new ProgressBar($output, count($templates));
			$progress->start();
		}
		$i = 0;
		foreach($templates as $template) {
			if($progress) {
				$progress->setMessage($template, 'file');
			}
			$templateEngine->loadTemplate($template);
			if($progress) {
				$progress->advance();
			}
			$i++;
		}
		if($progress) {
            $progress->finish();
			$output->writeln('');
		}

		if($i > 0) {
			$output->writeln('<info>Compiled '.$i.' templates</info>');
		} else {
			$output->writeln('<error>Error: no templates compiled</error>');
		}
	}

}
