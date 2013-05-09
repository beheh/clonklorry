<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Addon extends Presenter {

	public function get() {
		$this->security->requireLogin();

		$this->display('publish/addon.twig');
	}

}
