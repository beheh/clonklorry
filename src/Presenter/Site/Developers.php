<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Developers extends Presenter {

	public function get() {
		$this->display('site/developers.twig');
	}

}
