<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\ModelValueInvalidException;

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
		$password = filter_input(INPUT_POST, 'password');
		$password_repeat = filter_input(INPUT_POST, 'password-repeat');

		$this->context['username'] = $username;
		$this->context['email'] = $email;

		$errors = array();

		$user = ModelFactory::build('User');

		if(ModelFactory::build('User')->byUsername($username)) {
			$errors[] = gettext('Username already taken.');
		} else {
			try {
				$user->setUsername($username);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Username is %s.'), $e->getMessage());
			}
		}

		if($email && ModelFactory::build('User')->byEmail($email)) {
			$errors[] = sprintf(gettext('Email address is %s.'), sprintf('already used'));
		} else {
			try {
				$user->setEmail($email);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Email address is %s.'), gettext('invalid'));
			}
		}

		if($password !== $password_repeat) {
			$errors[] = gettext('Passwords do not match.');
		} else {
			try {
				$user->setPassword($password);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Password is %s.'), $e->getMessage());
			}
		}

		$user->setLanguage($this->localisation->getDisplayLanguage());

		if(empty($errors)) {
			if($user->save()) {
				$this->redirect('/login?registered='.urlencode($username));
			} else {
				$this->error('register', gettext('There was an error registering.'));
			}
		} else {
			$this->error('register', implode('<br>', $errors));
		}

		$this->get();
	}

}