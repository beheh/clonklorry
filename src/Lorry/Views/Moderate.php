<?php

namespace Lorry\Views;

use Lorry\View;
use Lorry\Models\User;

class Moderate extends View {

	protected function allow() {
		return false;
	}

	protected function allowUser(User $user) {
		return $user->isModerator();
	}

	protected function render() {
	}

}