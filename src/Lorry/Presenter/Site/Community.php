<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Community extends Presenter {

	public function get() {
		$this->display('site/community.twig');
	}

}