<?php

namespace Lorry\Email;

class Welcome extends Activate {

	public function write() {
		$this->context['contact_url'] = $this->config->get('base').'/contact';
		$this->render('welcome.twig');
	}

}
