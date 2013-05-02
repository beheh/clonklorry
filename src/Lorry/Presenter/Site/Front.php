<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Front extends Presenter {
	public function get() {
		$this->twig->display('site/front.twig');
	}
}