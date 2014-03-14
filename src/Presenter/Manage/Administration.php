<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;

class Administration extends Presenter {

	public function get() {
		$this->security->requireAdministrator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$this->display('manage/administration.twig');
	}

}
