<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\Exception\ModelValueInvalidException;
use Exception;

class User extends Model {

	public function __construct() {
		parent::__construct('user', array(
			'username' => 'string(3,16)',
			'secret' => 'string(255)',
			'password' => 'string(255)',
			'email' => 'string(255)',
			'activated' => 'boolean',
			'clonkforge' => 'int',
			'github' => 'string',
			'language' => 'string(5,5)',
			'oauth-openid' => 'string(255)',
			'oauth-google' => 'string(255)',
			'oauth-facebook' => 'string(255)'));
	}

	public function setUsername($username) {
		$this->validateString($username, 3, 16);
		if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
			throw new ModelValueInvalidException(gettext('invalid'));
		}
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
		if(empty($password))
			return false;
		return password_verify($password, $this->getValue('password')) === true;
	}

	public function setEmail($email) {
		$this->validateEmail($email);
		if($this->setValue('email', $email)) {
			if(!$this->modified()) {
				return true;
			}
			return $this->setValue('activated', false);
		}
		return false;
	}

	public function getEmail() {
		return $this->getValue('email');
	}

	public final function byEmail($email) {
		return $this->byValue('email', $email);
	}

	public final function isActivated() {
		return $this->getValue('activated');
	}

	public final function activate() {
		return $this->setValue('activated', true);
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

	public final function setClonkforgeUrl($clonkforge) {
		$scanned = array('');
		if($clonkforge) {
			$this->validateUrl($clonkforge);
			$clonkforge = preg_replace('|^(http://)?(www\.)?(.*)$|', 'http://$3', $clonkforge);
			$scanned = sscanf($clonkforge, $this->config->get('clonkforge'));
			if(count($scanned) != 1 || empty($scanned[0])) {
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
		} else {
			$clonkforge = null;
		}
		return $this->setValue('clonkforge', $clonkforge);
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
		else {
			$github = null;
		}
		return $this->setValue('github', $github);
	}

	public final function getGithub() {
		return $this->getValue('github');
	}

	public final function hasOauth($provider) {
		switch($provider) {
			case 'openid':
				return $this->getValue('oauth-openid') != null;
				break;
			case 'google':
				return $this->getValue('oauth-google') != null;
				break;
			case 'facebook':
				return $this->getValue('oauth-facebook') != null;
				break;
		}
		throw new Exception('invalid OAuth provider');
	}

	public final function byOauth($provider, $uid) {
		switch($provider) {
			case 'openid':
				return $this->byValue('oauth-openid', $uid);
				break;
			case 'google':
				return $this->byValue('oauth-google', $uid);
				break;
			case 'facebook':
				return $this->byValue('oauth-facebook', $uid);
				break;
		}
		throw new Exception('invalid OAuth provider');
	}

	public final function setOauth($provider, $uid) {
		if(!$uid && !$this->hasPassword() && !$this->hasRemainingOauth($provider)) {
			// dissallow last oauth to be removed without a password
			throw new ModelValueInvalidException('the last remaining login method');
		}
		switch($provider) {
			case 'openid':
				return $this->setValue('oauth-openid', $uid);
				break;
			case 'google':
				return $this->setValue('oauth-google', $uid);
				break;
			case 'facebook':
				return $this->setValue('oauth-facebook', $uid);
				break;
		}
		throw new ModelValueInvalidException('not a valid OAuth provider');
	}

	protected final function hasRemainingOauth($except) {
		$providers = array('openid', 'google', 'facebook');
		$provider_count = 0;
		foreach($providers as $provider) {
			if($provider == $except) {
				continue;
			}
			if($this->getValue('oauth-'.$provider) != null) {
				$provider_count++;
			}
		}
		return $provider_count > 0;
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
