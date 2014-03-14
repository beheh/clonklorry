<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;

class Moderation extends Presenter {

	public function get() {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		$this->display('manage/moderation.twig');
	}

}
