<?php

namespace Lorry\Email;

use Lorry\Email;

class Feedback extends Email {

	public function write() {
		$this->setRecipent($this->config->get('legal_mail'));
		$this->render('feedback.twig');
	}

	public function setSender($sender) {
		$this->context['sender'] = $sender;
	}

	public function setFeedback($feedback) {
		$this->context['feedback'] = $feedback;
	}

}
