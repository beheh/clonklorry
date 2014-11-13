<?php

namespace Lorry\Job;

use Lorry\ModelFactory;

abstract class UserEmailJob extends EmailJob {

	/**
	 * @var \Lorry\Model\User;
	 */
	protected $recipent;

	public function getRecipent() {
		return $this->recipent->getEmail();
	}
	
	public function prepareEmail(\Lorry\Email $email, $args) {
		$user = ModelFactory::build('User')->byId($args['user']);
		$this->recipent = $user;
		parent::prepareEmail($email);
	}

}