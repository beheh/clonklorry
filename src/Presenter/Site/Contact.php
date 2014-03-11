<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Contact extends Presenter {

	public function get() {
		$user = false;
		if($this->session->authenticated()) {
			$user = $this->session->getUser();
		}

		$this->context['legal_address'] = $this->config->get('legal_mail');
		if($user) {
			$this->context['by'] = $user->getUsername();
		} else {
			$this->context['by'] = $_SERVER['REMOTE_ADDR'];
		}

		$this->display('site/contact.twig');
	}

	public function post() {
		$this->success('contact', gettext('Your message was sent. Thank you for your feedback.'));

		$user = false;
		if($this->session->authenticated()) {
			$user = $this->session->getUser();
		}
		if($user) {
			$by = $user->getUsername();
		} else {
			$by = $_SERVER['REMOTE_ADDR'];
		}

		$message = $this->mail->create()
				->setSubject('Feedback from '.$by)
				->setTo($this->config->get('legal_mail'))
				->setBody(filter_input(INPUT_POST, 'feedback'));
		$this->mail->send($message);
		
		$this->get();
	}

}
