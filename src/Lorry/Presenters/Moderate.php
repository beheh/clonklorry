<?php

namespace Lorry\Presenters;

use Lorry\Presenter;
use Lorry\Models\User;

class Moderate extends Presenter {

	protected function allow() {
		return false;
	}

	protected function allowUser(User $user) {
		return $user->isModerator();
	}

	protected function render() {
	}

}