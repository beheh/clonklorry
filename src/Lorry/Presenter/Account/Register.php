<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Register extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			return $this->redirect('/');
		}

		$this->display('account/register.twig');
	}

	public function post() {
		$username = filter_input(INPUT_POST, 'username');
		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$password = filter_input(INPUT_POST, 'password', FILTER_VALIDATE_EMAIL);
		$password_repeat = filter_input(INPUT_POST, 'password-repeat');

		$this->context['username'] = $username;
		$this->context['email'] = $email;

		$errors = array();


		$username_length = strlen($username);
		if(!$username || $username_length < 3 || $username_length > 16) {
			$this->context['username_invalid'] = true;
			$errors[] = gettext('Username invalid.');
		}

		if(!$email) {
			$errors[] = gettext('Email address is invalid.');
		}

		if($password !== $password_repeat) {
			$errors[] = gettext('Passwords do not match.');
		}

		if(!empty($errors)) {
			$this->error('register', implode('<br>', $errors));
		}

		$this->get();
	}

}