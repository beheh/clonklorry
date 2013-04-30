<?php

namespace Lorry\Service;

use Lorry\Service;

class Email extends Service {

	public function send($to, $template, $parameters = array()) {
		$body = $this->lorry->twig->render('email/' . $template . '.twig', $parameters);
		// @TODO mail-magic!
		return true;
	}

}