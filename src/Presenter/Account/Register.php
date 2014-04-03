<?php

namespace Lorry\Presenter\Account;

use Analog;
use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\EmailFactory;
use Lorry\Exception\ModelValueInvalidException;

class Register extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			return $this->redirect('/');
		}

		if(isset($_GET['oauth'])) {
			return $this->redirect($this->session->handleOauth());
		}

		if(isset($_GET['cancel'])) {
			unset($_SESSION['register_oauth']);
		}

		$this->context['oauth'] = false;
		if(isset($_SESSION['register_oauth'])) {
			$register = $_SESSION['register_oauth'];

			if($register['email'])
				$this->context['email'] = $register['email'];
			$this->context['provider'] = $register['provider'];
			$this->context['username_focus'] = true;

			$this->context['oauth'] = true;
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

		$oauth = false;
		if(isset($_SESSION['register_oauth']) && filter_input(INPUT_POST, 'use-oauth')) {
			$oauth = $_SESSION['register_oauth'];
		}

		$user = ModelFactory::build('User');

		if(ModelFactory::build('User')->byUsername($username)) {
			$errors[] = gettext('Username already taken.');
			$this->context['username_focus'] = true;
		} else {
			try {
				$user->setUsername($username);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Username is %s.'), $e->getMessage());
			}
		}

		if($email && ModelFactory::build('User')->byEmail($email)) {
			$errors[] = sprintf(gettext('Email address already used.'));
		} else {
			try {
				$user->setEmail($email);
			} catch(ModelValueInvalidException $e) {
				$errors[] = sprintf(gettext('Email address is %s.'), gettext('invalid'));
			}
		}

		if(!$oauth) {
			if($password !== $password_repeat) {
				$errors[] = gettext('Passwords do not match.');
			} else {
				try {
					$user->setPassword($password);
				} catch(ModelValueInvalidException $e) {
					$errors[] = sprintf(gettext('Password is %s.'), $e->getMessage());
				}
			}
		} else {
			$user->setOauth(strtolower($oauth['provider']), $oauth['uid']);
		}

		$user->setLanguage($this->localisation->getDisplayLanguage());

		if(empty($errors)) {
			if($user->save()) {
				Analog::info('adding user "'.$user->getUsername().'"');
				$registration = EmailFactory::build('Register');
				$registration->setRecipent($user->getEmail());
				$registration->setUsername($user->getUsername());
				$this->mail->send($registration, $user->getLanguage());
				if($oauth) {
					$this->session->start($user, true);
					$this->redirect('/');
				} else {
					$this->redirect('/login?registered='.urlencode($username));
				}
			} else {
				$this->error('register', gettext('There was an error registering.'));
			}
		} else {
			$this->error('register', implode('<br>', $errors));
		}

		$this->get();
	}

}
