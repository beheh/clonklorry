<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;

class Moderation extends Presenter {

	public function get() {
		$this->security->requireLogin();
		$this->security->requireModerator();

		$this->display('manage/moderation.twig');
	}

}
