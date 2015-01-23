<?php

namespace Lorry\Email;

use Lorry\Email;

class Login extends Email {

	public function write() {
		$this->context['email'] = $this->getRecipent();
		$this->render('login.twig');
	}

	public function setCode($code) {
		$this->context['code'] = $code;
	}

}
