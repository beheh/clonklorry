<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Identify extends Presenter {

	public function get() {
		$this->security->requireLogin();
		$this->offerIdentification();

		if($this->session->identified()) {
			$this->redirect('/');
		}
	}

	public function post() {
		$this->security->requireLogin();
		$this->security->requireValidState();

		$user = $this->session->getUser();
		if(!$this->session->identified() && $user->hasPassword() && isset($_POST['password'])) {
			if($user->matchPassword(filter_input(INPUT_POST, 'password'))) {
				$this->session->identify();
			}
		}

		$return = filter_input(INPUT_POST, 'return');
		if(!$return) {
			$return = '/';
		}

		if($this->session->identified()) {
			$this->redirect($return);
		}

		$this->get();
	}

}
