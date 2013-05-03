<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Register extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			return $this->redirect('/');
		}
		$this->twig->display('account/register.twig');
	}

	public function post() {
		return $this->get();
	}

}