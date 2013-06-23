<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;

class Administration extends Presenter {

	public function get() {
		$this->security->requireAdministrator();

		$this->display('manage/administration.twig');
	}

	public function post() {
		$this->security->requireAdministrator();

		return $this->get();
	}

}
