<?php

namespace Lorry\Model;

use Lorry\Model;

class User extends Model {

	public function __construct() {
		parent::__construct('user', array(
			'username' => 'varchar(16)',
			'secret' => 'varchar(255)',
			'password' => 'varchar(255)',
			'email' => 'varchar(255)',
			'clonkforge' => 'int',
			'github' => 'string',
			'language' => 'varchar(5)'));
	}

	const USERNAME_LENGTH_MIN = 3;
	const USERNAME_LENGTH_MAX = 16;
	const USERNAME_OK = 1;
	const USERNAME_TOO_SHORT = 2;
	const USERNAME_TOO_LONG = 3;

	public function setUsername($username) {
		if(strlen($username) < self::USERNAME_LENGTH_MIN) {
			return self::USERNAME_TOO_SHORT;
		}
		if(strlen($username) > self::USERNAME_LENGTH_MAX) {
			return self::USERNAME_TOO_LONG;
		}
		return $this->setValue('username', $username) && self::USERNAME_OK;
	}

	public final function byUsername($username) {
		return $this->byValue('username', $username);
	}

	public function getUsername() {
		return $this->getValue('username');
	}

	const PASSWORD_LENGTH_MIN = 8;
	const PASSWORD_LENGTH_MAX = 256;
	const PASSWORD_OK = 1;
	const PASSWORD_TOO_SHORT = 2;
	const PASSWORD_TOO_LONG = 3;

	public final function setPassword($password) {
		if(strlen($password) < self::PASSWORD_LENGTH_MIN) {
			return self::PASSWORD_TOO_SHORT;
		}
		if(strlen($password) > self::PASSWORD_LENGTH_MAX) {
			return self::PASSWORD_TOO_LONG;
		}
		if(!empty($password)) {
			$hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
		} else {
			$hash = null;
		}
		return $this->setValue('password', $hash) && self::PASSWORD_OK;
	}

	public final function hasPassword() {
		return $this->getValue('password') != null;
	}

	public final function matchPassword($password) {
		if(empty($password))
			return false;
		return password_verify($password, $this->getValue('password')) === true;
	}

	const EMAIL_OK = 1;
	const EMAIL_INVALID = 2;

	public function setEmail($email) {
		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		if(!$email) {
			return self::EMAIL_INVALID;
		}
		return $this->setValue('email', $email) && self::EMAIL_OK;
	}

	public function getEmail() {
		return $this->getValue('email');
	}

	public final function byEmail($email) {
		return $this->byValue('email', $email);
	}

	public final function regenerateSecret() {
		$secret = base64_encode(openssl_random_pseudo_bytes(64));
		return $this->setValue('secret', $secret);
	}

	public final function getSecret() {
		return $this->getValue('secret');
	}

	public final function matchSecret($secret) {
		if(empty($secret))
			return false;
		return $this->match('secret', $secret);
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

	public final function setLanguage($language) {
		return $this->setValue('language', $language);
	}

	public final function getLanguage() {
		return $this->getValue('language');
	}

	public function __toString() {
		return $this->getUsername().'';
	}

}