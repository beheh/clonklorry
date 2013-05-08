<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Release extends Presenter {

	public function get() {
		$this->security->requireLogin();

		$this->display('publish/release.twig');
	}

}