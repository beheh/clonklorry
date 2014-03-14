<?php

namespace Lorry\Email;

use Lorry\Email;

class Activate extends Email {

	public function write() {
		$this->context['email'] = $this->getRecipent();
		$this->render('activate.twig');
	}

	public function setUrl($url) {
		$this->context['url'] = $url;
	}

}
