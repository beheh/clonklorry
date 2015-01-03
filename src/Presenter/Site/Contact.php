<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;
use Lorry\EmailFactory;

class Contact extends Presenter {

	public function get() {
		$user = false;
		if($this->session->authenticated()) {
			$user = $this->session->getUser();
		}

		$this->context['hide_greeter'] = true;
		$this->context['address'] = $this->config->get('contact/address');
		$this->context['legal_address'] = $this->config->get('contact/legal');
		if($user) {
			$this->context['by'] = $user->getUsername();
		} else {
			$this->context['by'] = $_SERVER['REMOTE_ADDR'];
		}

		$this->display('site/contact.twig');
	}

	public function post() {

		$user = false;
		if($this->session->authenticated()) {
			$user = $this->session->getUser();
		}
		if($user) {
			$by = $user->getUsername();
		} else {
			$by = $_SERVER['REMOTE_ADDR'];
		}

		$text = nl2br(htmlspecialchars(filter_input(INPUT_POST, 'feedback', FILTER_SANITIZE_STRING)));

		$feedback = EmailFactory::build('Feedback');

		if($user && $user->isActivated()) {
			$feedback->setReplyTo($user->getEmail());
		}

		$feedback->setSender($by);
		$feedback->setFeedback($text);

		if($this->mail->send($feedback)) {
			$this->success('contact', gettext('Your message was sent. Thank you for your feedback.'));
		} else {
			$this->error('contact', gettext('Sorry, your feedback couldn\'t be sent.'));
		}

		$this->get();
	}

}
