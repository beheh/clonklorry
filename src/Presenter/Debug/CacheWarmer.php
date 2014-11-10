<?php

namespace Lorry\Presenter\Debug;

use Analog;
use Lorry\Presenter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;

class CacheWarmer extends Presenter {

	public function get() {

		header('Content-Type: text/plain');
		
		$cache = $this->twig->getCache();
		if(!$cache) {
			echo 'Cache is not in use.';
			return;
		}

		$this->twig->clearTemplateCache();
		$cache = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache), RecursiveIteratorIterator::LEAVES_ONLY);
		$templates = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../app/templates'), RecursiveIteratorIterator::LEAVES_ONLY);
		
		$time = microtime(true);
		foreach($cache as $file) {
			if(!$file->isFile() || $file->getBasename() === '.gitignore') {
				continue;
			}
			unlink($file->getPathname());
		}
		Analog::info('Cleared Twig cache');

		$i = 0;
		foreach($templates as $file) {
			if(!$file->isFile() || $file->getExtension() !== 'twig') {
				continue;
			}
			$this->twig->loadTemplate(substr($file->getPathname(), strlen('../app/templates/')));
			$i++;
		}
		Analog::info('Cached '.$i.' Twig templates');

		$duration = round((microtime(true) - $time) / 1000, 5);
		if(!$duration) {
			$duration = '<1';
		}
		
		echo 'Done. Cleared cache and recached '.$i.' templates in '.$duration.'s.';
	}

}
