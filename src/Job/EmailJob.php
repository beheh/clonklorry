<?php

namespace Lorry\Job;

use Lorry\Job;

abstract class EmailJob extends Job {
	abstract function getEmail();
	
	
	public static function getQueue() {
		return 'email';
	}
	
	public function perform() {
		$email = EmailFactory::build($this->getEmail());
		$email->setRecipent($user->getEmail());
		$email->setUrl($url);
		$email->setUsername($user->getUsername());
		$this->mail->send($email);
	}
}