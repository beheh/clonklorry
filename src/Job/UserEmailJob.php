<?php

namespace Lorry\Job;

use Lorry\ModelFactory;
use Lorry\Email;

abstract class UserEmailJob extends EmailJob {

	/**
	 * @var \Lorry\Model\User;
	 */
	protected $user;

	public function getRecipent() {
		return $this->user->getEmail();
	}
	
	public function prepareEmail(Email $email, $args) {
		$user = $this->persistence->build('User')->byId($args['user']);
		$this->user = $user;
		$this->localisation->silentLocalize($user->getLanguage());
		$email->setUsername($user->getUsername());
		parent::prepareEmail($email);
	}

}
