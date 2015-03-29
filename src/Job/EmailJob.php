<?php

namespace Lorry\Job;

use Lorry\Job;
use Lorry\Email;

abstract class EmailJob extends Job {

	abstract function getEmail();

	abstract function getRecipent();

	public function getQueue() {
		return 'email';
	}

	public function prepareEmail(Email $email) {
		$email->setRecipent($this->getRecipent());
	}

	public function perform() {
		$email = $this->mail->build($this->getEmail());
		$this->prepareEmail($email, $this->args);
		$this->mail->send($email);
	}

}
