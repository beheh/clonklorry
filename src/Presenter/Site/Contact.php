<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\ModelValueInvalidException;

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

		$ticket = ModelFactory::build('Ticket');

		$errors = array();

		if($user) {
			$ticket->setUser($user->getId());
		}

		try {
			$ticket->setRequest(htmlspecialchars(filter_input(INPUT_POST, 'feedback', FILTER_SANITIZE_STRING)));
		} catch(ModelValueInvalidException $e) {
			$errors[] = sprintf(gettext('Message text is %s.'), $e->getMessage());
		}

		if(empty($errors)) {
			$existing = ModelFactory::build('Ticket')->byHash($ticket->getHash());
			if($existing) {
				$errors[] = gettext('This message has already been sent.');
			}
		}

		if(empty($errors)) {
			if($ticket->save()) {
				$this->success('contact', gettext('Thank you for your message, we\'ll take a look at it.'));
			} else {
				$this->error('contact', gettext('Sorry, your message couldn\'t be saved.'));
			}
		} else {
			$this->error('contact', implode('<br>', $errors));
		}

		$this->get();
	}

}
