<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Register extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			return $this->redirect('/');
		}
		$this->display('account/register.twig', $this->context);
	}

	public function post() {
		$username = filter_input(INPUT_POST, 'username');
		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$password = filter_input(INPUT_POST, 'password', FILTER_VALIDATE_EMAIL);
		$password_repeat = filter_input(INPUT_POST, 'password-repeat');
		$this->get();
	}

}