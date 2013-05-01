<?php

namespace Lorry\Presenters;

use Lorry\Presenter;
use Lorry\Models\User;

class Admin extends Presenter {

	protected function allow() {
		return false;
	}

	protected function allowUser(User $user) {
		return $user->isAdministrator();
	}

	protected function render() {

	}

}