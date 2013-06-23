<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;

class Moderation extends Presenter {

	public function get() {
		$this->offerIdentification();
		$this->security->requireModerator();

		$this->display('manage/moderation.twig');
	}

	public function post() {
		$this->security->requireModerator();

		return $this->get();
	}

}
