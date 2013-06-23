<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\Exception\ModelValueInvalidException;

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

	public function setUsername($username) {
		$this->validateString($username, 3, 16);
		return $this->setValue('username', $username);
	}

	public final function byUsername($username) {
		return $this->byValue('username', $username);
	}

	public function getUsername() {
		return $this->getValue('username');
	}

	public final function setPassword($password) {
		$this->validateString($password, 8, 255);
		if(!empty($password)) {
			$hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
		} else {
			$hash = null;
		}
		return $this->setValue('password', $hash);
	}

	public final function hasPassword() {
		return $this->getValue('password') != null;
	}

	public final function matchPassword($password) {
		if(empty($password)) return false;
		return password_verify($password, $this->getValue('password')) === true;
	}

	public function setEmail($email) {
		$this->validateEmail($email);
		return $this->setValue('email', $email);
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
		if(empty($secret)) return false;
		return $this->match('secret', $secret);
	}

	public final function isAdministrator() {
		return $this->getId() == 1;
	}

	public final function isModerator() {
		return $this->getId() == 1;
	}

	public final function setClonkforgeUrl($clonkforge) {
		if($clonkforge) {
			$this->validateUrl($clonkforge);
			$scanned = sscanf($clonkforge, $this->config->get('clonkforge'));
			if(count($scanned) != 1) {
				throw new ModelValueInvalidException(gettext('not a matching Clonk Forge URL'));
			}
		}
		try {
			return $this->setClonkforge($scanned[0]);
		} catch(ModelValueInvalidException $e) {
			throw new ModelValueInvalidException(gettext('not a valid Clonk Forge URL'));
		}
	}

	public final function setClonkforge($clonkforge) {
		if($clonkforge) {
			$this->validateNumber($clonkforge);
			if($clonkforge < 1) {
				throw new ModelValueInvalidException(gettext('not a valid Clonk Forge profile id'));
			}
		}
		return $this->setValue('clonkforge', intval($clonkforge));
	}

	public final function getClonkforge() {
		return $this->getValue('clonkforge');
	}

	public final function getClonkforgeUrl() {
		$clonkforge = $this->getClonkforge();
		if($clonkforge) {
			return sprintf($this->config->get('clonkforge'), $this->getClonkforge());
		}
		return '';
	}

	public final function setGithub($github) {
		if($github) {
			$this->validateString($github, 1, 255);
			if(!preg_match('#^'.$this->config->get('github_name').'$#', $github)) {
				throw new ModelValueInvalidException(gettext('not a valid GitHub name'));
			}
		}
		return $this->setValue('github', $github);
	}

	public final function getGithub() {
		return $this->getValue('github');
	}

	public final function setLanguage($language) {
		$this->validateLanguage($language);
		return $this->setValue('language', $language);
	}

	public final function getLanguage() {
		return $this->getValue('language');
	}

	public function __toString() {
		return $this->getUsername().'';
	}

}