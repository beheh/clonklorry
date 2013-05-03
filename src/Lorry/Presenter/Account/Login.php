<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Login extends Presenter {

	private $context = array();

	public function get() {
		if($this->session->authenticated()) {
			return $this->redirect('/');
		}

		if(isset($_GET['by-email'])) {
			$this->context['email'] = true;
		}
		$this->twig->display('account/login.twig', $this->context);
	}

	public function post() {
		if(isset($_GET['by-email'])) {
			$this->context['email_focus'] = true;
		}
		return $this->get();
	}

}