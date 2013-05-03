<?php

namespace Lorry\Service;

use Lorry\ModelFactory;
use Lorry\Model\User;
use Exception;

class SessionService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	protected $started = false;
	protected $user = false;

	public function __construct() {
		session_name('lorry_session');
	}

	public final function start(User $user, $remember = false) {
		$this->ensureSession();
		if(!$user->isLoaded()) {
			throw new Exception('user is not loaded or does not exist');
		}
		$this->authenticate($user);
		if($remember == true) {
			try {
				$this->remember();
			} catch(Exception $ex) {
				if($this->config->get('debug')) {
					throw $ex;
				}
			}
			setcookie('lorry_forget', '', 0, '/');
		} else {
			setcookie('lorry_forget', '1', time() + 60 * 60 * 24 * 365, '/');
		}
		return true;
	}

	public final function authenticate(User $user) {
		$this->ensureSession();
		$_SESSION['user'] = $user->getId();
		$_SESSION['secret'] = $user->getSecret();
	}

	public final function remember() {
		$this->ensureUser();
		$secret = $this->user->getSecret();
		if(!empty($secret)) {
			setcookie('lorry_login', '$'.$this->user->getId().'$'.$secret, time() + 60 * 60 * 24 * 365, '/');
		}
	}

	public final function shouldRemember() {
		if(isset($_COOKIE['lorry_forget']) && $_COOKIE['lorry_forget'] == '1') {
			return false;
		}
		return true;
	}

	public final function authenticated() {
		if(!isset($_COOKIE['lorry_session']) && !isset($_COOKIE['lorry_login']))
			return false;
		$this->ensureUser();
		return $this->user !== false;
	}

	/**
	 *
	 * @return \Lorry\Model\User
	 * @throws Exception
	 */
	public final function getUser() {
		if(!$this->authenticated()) {
			throw new Exception('session is not authenticated');
		}
		$this->ensureUser();
		return $this->user;
	}

	/**
	 * Ensures that a possibly availabe user is loaded.
	 */
	protected final function ensureUser() {
		$this->ensureSession();
		if($this->user) {
			return true;
		}
		if(isset($_SESSION['user']) && is_numeric($_SESSION['user'])) {
			$this->user = ModelFactory::build('User')->byId($_SESSION['user']);
			if($this->user->getSecret() != $_SESSION['secret']) {
				$this->user = false;
			}
			if($this->user === false) {
				$this->logout();
			}
		} else if(isset($_COOKIE['lorry_login'])) {
			$this->user = false;
			$login = explode('$', $_COOKIE['lorry_login']);
			if(count($login) == 3 && is_numeric($login[1])) {
				$user = ModelFactory::build('User')->byId($login[1]);
				if($user && !empty($login[2]) && $user->matchSecret($login[2])) {
					$this->user = $user;
				}
			}
			if($this->user === false) {
				$this->logout();
			}
		}
		return true;
	}

	protected final function ensureSession() {
		if(!$this->started) {
			session_start();
			$this->started = true;
		}
		return true;
	}

	public final function logout() {
		$this->forget();
		$this->end();
		return true;
	}

	public final function forget() {
		setcookie('lorry_login', '', 0, '/');
		return true;
	}

	public final function end() {
		if(session_status() == PHP_SESSION_ACTIVE) {
			session_unset();
			session_destroy();
		}
		setcookie('lorry_session', '', 0, '/');
		return true;
	}

}