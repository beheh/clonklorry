<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class About extends Presenter {

	public function get() {
		$this->display('site/about.twig');
	}

}