<?php

class Lorry_Model_User extends Lorry_Model {

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry, 'user', array('username', 'email', 'password'));
	}

	public function getUsername() {
		return $this->getValue('username');
	}

	public final function byUsername($username) {
		return $this->byValue('username', $username);
	}

	public final function matchPassword($password) {
		return $this->match('password', sha1($password));
	}

	public function __toString() {
		return $this->getUsername.'';
	}

}

