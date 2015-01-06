<?php

namespace Lorry\Presenter\Manage\Administrator;

use Lorry\Presenter;

class Portal extends Presenter {

	public function get() {
		$this->security->requireAdministrator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$this->display('manage/administrator/portal.twig');
	}

}
