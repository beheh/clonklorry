<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Login extends Presenter {

	private $context = array();

	public function get() {
		if($this->session->authenticated()) {
			$this->redirect('/');
			return;
		}

		if(!isset($this->context['remember'])) {
			$this->context['remember'] = true;
		}
		if(isset($_GET['by-email']) || isset($_COOKIE['lorry_login_email'])) {
			$this->context['email'] = true;
		}

		$this->twig->display('account/login.twig', $this->context);
	}

	public function post() {
		if(isset($_GET['by-email'])) {
			$this->context['email_focus'] = true;
			$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
			$user = ModelFactory::build('User')->byEmail($email);
			if($user) {
				setcookie('lorry_login_email', '1', time() + 60 * 60 * 24 * 365, '/');
			}
		} else {
			$user = ModelFactory::build('User')->byUsername(filter_input(INPUT_POST, 'username', FILTER_DEFAULT));
			$remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN);
			$this->context['remember'] = $remember || false;
			if($user) {
				setcookie('lorry_login_email', '', 0, '/');
				if($user->matchPassword(filter_input(INPUT_POST, 'password', FILTER_DEFAULT))) {
					$this->session->start($user, $remember);
					$this->redirect('/');
					return;
				}
			}
		}
		$this->get();
	}

}