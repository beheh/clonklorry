<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Model\User;

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
			switch($user->setUsername($username)) {
				case User::USERNAME_OK:
					break;
				case User::USERNAME_TOO_SHORT:
					$errors[] = gettext('Username too short.');
					break;
				case User::USERNAME_TOO_LONG:
					$errors[] = gettext('Username too long.');
					break;
				default:
					$errors[] = gettext('Username invalid.');
					break;
			}
		}

		if($email && ModelFactory::build('User')->byEmail($email)) {
			$errors[] = gettext('Email address already used.');
		} else {
			switch($user->setEmail($email)) {
				case User::EMAIL_OK:
					break;
				default:
					$errors[] = gettext('Email address invalid.');
					break;
			}
		}

		if($password !== $password_repeat) {
			$errors[] = gettext('Passwords do not match.');
		} else {
			switch($user->setPassword($password)) {
				case User::PASSWORD_OK:
					break;
				case User::PASSWORD_TOO_SHORT:
					$errors[] = gettext('Password too short.');
					break;
				case User::PASSWORD_TOO_LONG:
					$errors[] = gettext('Password too long.');
					break;
				default:
					$errors[] = gettext('Password invalid.');
					break;
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