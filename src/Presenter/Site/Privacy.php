<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Privacy extends Presenter {

	public function get() {
		$this->display('site/privacy.twig');
	}

}