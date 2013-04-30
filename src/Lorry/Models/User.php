<?php

namespace Lorry\Models;

use Lorry\Model;
use Lorry\Environment;

class User extends Model {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry, 'user', array(
			'username' => 'varchar(16)',
			'secret' => 'varchar(255)',
			'password' => 'varchar(255)',
			'email' => 'varchar(255)',
			'clonkforge' => 'int',
			'github' => 'string'));
	}

	public function getUsername() {
		return $this->getValue('username');
	}

	public function setUsername($username) {
		return $this->setValue('username', $username);
	}

	public final function byUsername($username) {
		return $this->byValue('username', $username);
	}

	public function getEmail() {
		return $this->getValue('email');
	}

	public function setEmail($email) {
		return $this->setValue('email', $email);
	}

	public final function byEmail($email) {
		return $this->byValue('email', $email);
	}

	public final function regenerateSecret() {
		$secret = $this->lorry->security->castSecret();
		if(!$secret)
			return false;
		return $this->setValue('secret', $secret);
	}

	public final function getSecret() {
		return $this->getValue('secret');
	}

	public final function matchSecret($secret) {
		return $this->match('secret', $secret);
	}

	public final function setPassword($password) {
		$hash = $this->lorry->security->hash($password);
		if(!$hash)
			return false;
		return $this->setValue('password', $hash);
	}

	public final function hasPassword() {
		return $this->getValue('password') != '';
	}

	public final function matchPassword($password) {
		return $this->lorry->security->verify($password, $this->getValue('password'));
	}

	public final function isAdministrator() {
		return $this->getId() == 1;
	}

	public final function isModerator() {
		return $this->getId() == 1;
	}

	public final function setClonkforge($clonkforge) {
		return $this->setValue('clonkforge', intval($clonkforge));
	}

	public final function getClonkforge() {
		return $this->getValue('clonkforge');
	}

	public final function setGithub($github) {
		return $this->setValue('github', $github);
	}

	public final function getGithub() {
		return $this->getValue('github');
	}

	public function __toString() {
		return $this->getUsername.'';
	}

}