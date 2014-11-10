<?php

namespace Lorry\Email;

use Lorry\Email;

class Login extends Email {

	public function write() {
		$this->context['email'] = $this->getRecipent();
		$this->render('login.twig');
	}

	public function setUrl($url) {
		$this->context['url'] = $url;
	}

}
