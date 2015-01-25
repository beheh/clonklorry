<?php

namespace Lorry\Presenter\Debug;

use Lorry\Presenter;

class CacheClearer extends Presenter {

	public function get() {
		$this->twig->clearCacheFiles();
		Analog::info('Cleared Twig cache');

		echo 'Done. Cleared cache and recached '.$i.' templates.';
	}

}
