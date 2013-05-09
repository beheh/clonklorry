<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;

class Administration extends Presenter {

	public function get() {
		$this->security->requireLogin();
		$this->security->requireAdministrator();

		$this->display('manage/administration.twig');
	}

}
