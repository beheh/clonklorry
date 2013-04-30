<?php

namespace Lorry\Views;

use Lorry\View;
use Lorry\Models\User;

class Admin extends View {

	protected function allow() {
		return false;
	}

	protected function allowUser(User $user) {
		return $user->isAdministrator();
	}

	protected function render() {

	}

}