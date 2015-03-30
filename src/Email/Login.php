<?php

namespace Lorry\Email;

use Lorry\Email;

class Login extends Email {

	public function write() {
		$this->context['email'] = $this->getRecipent();
		$this->render('login.twig');
	}

	public function setLoginUrl($url) {
		$this->context['login_url'] = $url;
	}

}
