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
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../app/templates'));
		$results = new RegexIterator($iterator, '/^.+\.twig$/i', RecursiveRegexIterator::GET_MATCH);

		$time = microtime(true);

		$this->twig->clearCacheFiles();
		Analog::info('Cleared Twig cache');

		$i = 0;
		foreach($results as $file => $array) {
			$filename = substr($file, 17);
			$this->twig->loadTemplate($filename);
			$i++;
		}
		Analog::info('Cached '.$i.' Twig templates');

		$duration = round((microtime(true) - $time) / 1000, 5);
		if(!$duration) {
			$duration = '<1';
		}
		
		header('Content-Type: text/plain');
		echo 'Done. Cleared cache and recached '.$i.' templates in '.$duration.'s.';
	}

}
