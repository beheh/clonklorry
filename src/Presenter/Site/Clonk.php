<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Clonk extends Presenter {

	public function get() {
		$this->display('site/clonk.twig');
	}

}