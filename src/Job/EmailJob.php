<?php

namespace Lorry\Job;

use Lorry\Job;
use Lorry\Email;
use Lorry\EmailFactory;

abstract class EmailJob extends Job {

	abstract function getEmail();

	abstract function getRecipent();

	public final static function getQueue() {
		return 'email';
	}

	public function prepareEmail(Email $email) {
		$email->setRecipent($this->getRecipent());
	}

	public function perform() {
		$email = EmailFactory::build($this->getEmail());
		$this->prepareEmail($email, $this->args);
		$this->mail->send($email);
	}

}
