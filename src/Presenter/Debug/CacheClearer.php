<?php

namespace Lorry\Presenter\Debug;

use Lorry\Presenter;
use Analog\Analog as Logger;

class CacheClearer extends Presenter {

	public function get() {
		$this->twig->clearCacheFiles();
		Logger::info('Cleared Twig cache');

		echo 'Done. Cleared cache and recached '.$i.' templates.';
	}

}
