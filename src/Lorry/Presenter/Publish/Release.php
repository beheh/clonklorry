<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;

class Release extends Presenter {

	public function get($game, $addon, $version) {
		$this->security->requireLogin();
		$user = $this->session->getUser();

		$this->display('publish/release.twig');
	}

}