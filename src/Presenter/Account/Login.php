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
		if(!isset($this->context['remember']) && !$this->session->getFlag('login_forget')) {
			$this->context['remember'] = true;
		}
		if(isset($_POST['email_submit']) || $this->session->getFlag('login_email')) {
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
		if(isset($_GET['connect'])) {
			$this->context['connect'] = true;
			if(!$this->hasAlert('login')) {
				$this->warning('login', gettext('Sign in to use this service.'));
			}
		}
		if(isset($_GET['unknown-oauth']) && !$this->hasAlert('login')) {
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
				$this->session->setFlag('login_email');
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
			$remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN) || false;
			// take username to next page
			$this->context['username'] = $username;
			// set remember checkmark to persist after post
			$this->context['remember'] = $remember;
			if($user) {
				$this->context['username_exists'] = true;
				if($user->matchPassword(filter_input(INPUT_POST, 'password', FILTER_DEFAULT))) {
					// do not show email login by default
					$this->session->unsetFlag('login_email');
					// log user in
					$this->session->start($user, $remember, true);
					if(!$remember) {
						$this->session->setFlag('login_forget');
					} else {
						$this->session->unsetFlag('login_forget');
					}
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
