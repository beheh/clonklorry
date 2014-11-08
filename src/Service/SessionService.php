<?php

namespace Lorry\Service;

use Lorry\ModelFactory;
use Lorry\Model\User;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\OutputCompleteException;
use Lorry\Exception;

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

	public final function start(User $user, $remember = false, $identify = false) {
		$this->ensureSession();
		if(!$user->isLoaded()) {
			throw new Exception('user is not loaded or does not exist');
		}
		$this->authenticate($user);
		if($identify) {
			$this->identify();
		}
		if($remember == true) {
			$this->remember();
			if(isset($_COOKIE['lorry_forget'])) {
				setcookie('lorry_forget', '', 0, '/');
			}
		} else {
			setcookie('lorry_forget', '1', time() + 60 * 60 * 24 * 365, '/');
		}
		return true;
	}

	public final function refresh() {
		$this->ensureSession();
		$this->ensureUser();
		$this->ensureSecret($this->user);
		session_regenerate_id();
		$_SESSION['secret'] = $this->user->getSecret();
	}

	protected final function authenticate(User $user) {
		$this->ensureSession();
		$this->ensureSecret($user);
		session_regenerate_id();
		$this->user = $user;
		$_SESSION['user'] = $user->getId();
		$_SESSION['secret'] = $user->getSecret();
		$_SESSION['identified'] = false; // whether the user has personally identifed via password, as opposed to login cookie
	}

	public final function identify() {
		$this->ensureSession();
		$_SESSION['identified'] = true;
	}

	public final function identified() {
		$this->ensureUser();
		return $this->user->hasPassword() && isset($_SESSION['identified']) && $_SESSION['identified'] == true;
	}

	public final function remember() {
		$this->ensureUser();
		$this->ensureSecret($this->user);
		$secret = $this->user->getSecret();
		setcookie('lorry_login', '$'.$this->user->getId().'$'.$secret, time() + 60 * 60 * 24 * 365, '/');
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

	protected final function castState() {
		$state = bin2hex(openssl_random_pseudo_bytes(16));
		return $state;
	}

	protected final function ensureState() {
		$this->ensureSession();
		if(!isset($_SESSION['state']) || !$_SESSION['state']) {
			$this->regenerateState();
		}
	}

	public final function getState() {
		$this->ensureState();
		return $_SESSION['state'];
	}

	public final function regenerateState() {
		$this->ensureSession();
		$state = $this->castState();
		$_SESSION['state'] = $state;
		return $state;
	}

	public final function verifyState($state) {
		$this->ensureSession();
		if(!isset($_SESSION['state'])) {
			return false;
		}
		if(hash_equals($state, $_SESSION['state'])) {
			return true;
		}
		return false;
	}
	
	public final function setAuthorizationState($state) {
		$this->ensureSession();
		$_SESSION['authorization_state'] = $state;
	}
	
	public final function verifyAuthorizationState($state) {
		$this->ensureSession();
		if(!isset($_SESSION['authorization_state'])) {
			return false;
		}
		if(hash_equals($state, $_SESSION['authorization_state'])) {
			$this->clearAuthorizationState();
			return true;
		}
		$this->clearAuthorizationState();
		return false;
	}
	
	public final function clearAuthorizationState() {
		$this->ensureSession();
		unset($_SESSION['authorization_state']);
		return true;
	}

	/**
	 *
	 * @return \Lorry\Model\User
	 * @throws Exceptions
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
			$user = ModelFactory::build('User')->byId($_SESSION['user']);
			if($user && $user->matchSecret($_SESSION['secret'])) {
				$this->user = $user;
			} else {
				$this->logout();
			}
		} else if(isset($_COOKIE['lorry_login'])) {
			$user = false;
			$login = explode('$', $_COOKIE['lorry_login']);
			if(count($login) == 3 && is_numeric($login[1])) {
				$user = ModelFactory::build('User')->byId($login[1]);
				if($user && !empty($login[2]) && $user->matchSecret($login[2])) {
					$this->authenticate($user);
				}
			}
			if($this->user === false) {
				$this->logout();
			}
		}
		return true;
	}

	protected final function ensureSecret(User $user) {
		if($user->getSecret() == null) {
			$user->regenerateSecret();
			$user->save();
		}
		return true;
	}

	public final function ensureSession() {
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

	private static $OAUTH_PROVIDERS = array('openid', 'google', 'facebook');

	public final function handleOauth() {
		$provider = filter_input(INPUT_GET, 'oauth');
		if(!in_array($provider, self::$OAUTH_PROVIDERS)) {
			throw new FileNotFoundException();
		}
		$params = array();

		$gateway = '/auth/gateway/'.$provider;
		if($provider == 'openid') {
			$params[] = 'identity='.filter_input(INPUT_POST, 'openid-identity');
		}
		$returnto = filter_input(INPUT_GET, 'returnto');
		if($returnto) {
			$params[] = 'returnto='.$returnto;
		}
		if(!empty($params)) {
			$gateway .= '?'.implode('&', $params);
		}
		return $gateway;
	}

}
