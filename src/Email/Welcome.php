<?php

namespace Lorry\Email;

use Lorry\Email;

class Welcome extends Email {

	public function write() {
		$this->context['contact'] = '<a href="'.$this->config->get('base').'/contact">'.$this->config->get('base').'/contact</a>';
		$this->render('welcome.twig');
	}

}
