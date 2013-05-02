<?php

namespace Lorry\Presenter\Addon;

use \Lorry\Presenter;

class Overview extends Presenter {

	public function get($addon, $release = 'latest') {
		echo $addon.'-'.$release;
	}

}