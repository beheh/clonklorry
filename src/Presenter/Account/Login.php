<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Login extends Presenter {

	public function get() {
		if(isset($_SESSION['register_oauth'])) {
			unset($_SESSION['register_oauth']);
		}

		if($this->session->authenticated()) {
			$this->redirect('/');
			return;
		}

		if(isset($_GET['oauth'])) {
			return $this->redirect($this->session->handleOauth());
		}
		
		if(isset($_GET['returnto'])) {
			$this->context['returnto'] = filter_input(INPUT_GET, 'returnto');
		}

		if(!isset($this->context['remember']) && $this->session->shouldRemember()) {
			$this->context['remember'] = true;
		}
		if(isset($_POST['email_submit']) || isset($_COOKIE['lorry_login_email'])) {
			$this->context['email_visible'] = true;
		}
		if(isset($_POST['forgot_password'])) {
			$this->context['forgot_password'] = true;
		}
		if(isset($_GET['registered'])) {
			$this->context['username'] = $_GET['registered'];
			if($_GET['registered']) {
				$this->context['username_exists'] = true;
			}
			if(!$this->hasAlert('login')) {
				$this->success('login', gettext('Registration successful!'));
			}
		}
		if(isset($_GET['unknown-oauth'])) {
			$this->warning('login', gettext('Sign in to link this login service to your account.'));
		}


		$this->display('account/login.twig');
	}

	public function post() {
		if(isset($_POST['email-submit'])) {
			// login by email token
			$this->context['email_focus'] = true;
			$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
			$user = ModelFactory::build('User')->byEmail($email);
			if($user) {
				// show email by default in future
				setcookie('lorry_login_email', '1', time() + 60 * 60 * 24 * 365, '/');
				$this->job->submit('Login', array('user' => $user->getId()));
				$this->success('email', 'We\'ll send your email shortly.');
			} else {
				// email is unknown
				$this->error('email', gettext('Email address unknown.'));
			}
			$this->context['email'] = $email;
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
					if(isset($_COOKIE['lorry_login_email'])) {
						setcookie('lorry_login_email', '', 0, '/');
					}
					// log user in
					$this->session->start($user, $remember, true);
					$url = '/';
					$returnto = filter_input(INPUT_GET, 'returnto');
					if($returnto) {
						$url = $returnto;
					}
					$this->redirect($url);
					return;
				} else {
					// password is incorrect
					$this->error('login', gettext('Password wrong.'));
				}
			} else {
				// user does not exist
				$this->error('login', gettext('Username unknown.'));
			}
		}
		$this->get();
	}

}
