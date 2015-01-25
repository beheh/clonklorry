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

		$base = $lorry::PROJECT_ROOT.'/app/templates';

		$lorry->getTemplating()->clearTemplateCache();

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

		if($output->isVerbose()) {
			$progress = new ProgressBar($output, count($templates));
			$progress->start();
		}
		$i = 0;
		foreach($templates as $template) {
			if($output->isVerbose()) {
				$progress->setMessage($template, 'file');
			}
			//sleep(1);
			$lorry->getTemplating()->loadTemplate($template);
			if($output->isVerbose()) {
				$progress->advance();
			}
			$i++;
		}
		if($output->isVerbose()) {
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
