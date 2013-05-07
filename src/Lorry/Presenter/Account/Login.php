<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Login extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			$this->redirect('/');
			return;
		}

		if(!isset($this->context['remember']) && $this->session->shouldRemember()) {
			$this->context['remember'] = true;
		}
		if(isset($_GET['by-email']) || isset($_COOKIE['lorry_login_email'])) {
			$this->context['email_visible'] = true;
		}

		$this->display('account/login.twig', $this->context);
	}

	public function post() {
		if(isset($_GET['by-email'])) {
			// login by email token
			$this->context['email_focus'] = true;
			$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
			$user = ModelFactory::build('User')->byEmail($email);
			if($user) {
				// show email by default in future
				setcookie('lorry_login_email', '1', time() + 60 * 60 * 24 * 365, '/');
				// @TODO send mail token
			} else {
				// email is unknown
				$this->context['email'] = $email;
				$this->error('email', gettext('Email address is unknown.'));
			}
		} else if(isset($_GET['openid'])) {
			// login with openid
			$openid = filter_input(INPUT_GET, 'openid');
			// @TODO openid implementation
		} else {
			// login by username and password
			$username = filter_input(INPUT_POST, 'username', FILTER_DEFAULT);
			$user = ModelFactory::build('User')->byUsername($username);
			$remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN);
			// take username to next page
			$this->context['username'] = $username;
			// set remember checkmark to persist after post
			$this->context['remember'] = $remember || false;
			if($user) {
				$this->context['username_exists'] = true;
				if($user->matchPassword(filter_input(INPUT_POST, 'password', FILTER_DEFAULT))) {
					// do not show email login by default
					setcookie('lorry_login_email', '', 0, '/');
					// log user in
					$this->session->start($user, $remember);
					$this->redirect('/');
					return;
				} else {
					// password is incorrect
					$this->error('login', gettext('Password is wrong.'));
				}
			} else {
				// user does not exist
				$this->error('login', gettext('Username is unknown.'));
			}
		}
		$this->get();
	}

}