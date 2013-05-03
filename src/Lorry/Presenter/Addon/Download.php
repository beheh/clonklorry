<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;

class Download extends Presenter {

	public function get($addon, $release = 'latest') {
		echo $addon.'-'.$release;
	}

}