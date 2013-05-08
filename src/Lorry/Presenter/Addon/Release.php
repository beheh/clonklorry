<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;

class Release extends Presenter {

	public function get($gamename, $addonname, $release = 'latest') {
		$this->display('addon/release.twig');
	}

}