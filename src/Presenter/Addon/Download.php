<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;

class Download extends Presenter {

	public function get($gamename, $addonname, $version = 'latest') {
		echo 'download for '.$gamename.'/'.$addonname.'-'.$version;
	}

}