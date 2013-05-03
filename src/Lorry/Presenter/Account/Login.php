<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Login extends Presenter {

	private $context = array();

	public function get() {
		if($this->session->authenticated()) {
			return $this->redirect('/');
		}

		$this->context['remember'] = true;
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
			setcookie('lorry_login_email', '', 0, '/');
		}
		return $this->get();
	}

}