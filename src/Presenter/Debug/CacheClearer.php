<?php

namespace Lorry\Presenter\Debug;

use Lorry\Presenter;
use Analog\Analog;

class CacheClearer extends Presenter {

	public function get() {
		$this->twig->clearCacheFiles();
		Analog::info('Cleared Twig cache');

		echo 'Done. Cleared cache and recached '.$i.' templates.';
	}

}
