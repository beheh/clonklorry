<?php

namespace Lorry\Service;

use Lorry\Service;
use Lorry\Logger\LoggerFactoryInterface;
use Lorry\Model\User;
use Lorry\Exception\FileNotFoundException;
use InvalidArgumentException;
use Exception;

class SessionService extends Service {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function __construct(LoggerFactoryInterface $loggerFactory, ConfigService $config, PersistenceService $persistence) {
		parent::__construct($loggerFactory);
		$this->config = $config;
		$this->persistence = $persistence;
		session_name('lorry_session');
	}

    /**
     * @var bool
     */
	protected $started = false;

    /**
     * @var \Lorry\Model\User
     */
	protected $user = null;

	/**
	 * 
	 * @param \Lorry\Model\User $user
	 * @param bool $remember
	 * @param bool $identify
	 * @throws Exception
	 */
	final public function start(User $user, $remember = false, $identify = false) {
		$this->ensureSession();
		if(!$user->isLoaded()) {
			throw new InvalidArgumentException('user is not loaded or does not exist');
		}
		$this->authenticate($user);
		if($identify) {
			$this->identify();
		}
		if($remember === true) {
			$this->remember();
		}
	}

	final public function refresh() {
		$this->ensureSession();
		$this->ensureUser();
		$this->ensureSecret($this->user);
		session_regenerate_id(true);
		$_SESSION['secret'] = $this->user->getSecret();
	}

	/**
	 * 
	 * @param \Lorry\Model\User $user
	 */
	final protected function authenticate(User $user) {
		$this->ensureSession();
		$this->ensureSecret($user);
		session_regenerate_id(true);
		$this->regenerateState();
		$this->user = $user;
		$_SESSION['user'] = $user->getId();
		$_SESSION['secret'] = $user->getSecret();
		$_SESSION['identified'] = false; // whether the user has personally identifed via password, as opposed to login cookie
		$this->setFlag('knows_clonk', true);
	}

	final public function identify() {
		$this->ensureSession();
		$_SESSION['identified'] = true;
	}

	/**
	 * 
	 * @return bool
	 */
	final public function identified() {
		$this->ensureUser();
		return $this->user->hasPassword() && isset($_SESSION['identified']) && $_SESSION['identified'] === true;
	}

	final public function remember() {
		$this->ensureUser();
		$this->ensureSecret($this->user);
		$secret = $this->user->getSecret();
		setcookie('lorry_login', '$'.$this->user->getId().'$'.$secret, time() + 60 * 60 * 24 * 365, '/');
	}

	/**
	 * 
	 * @return bool
	 */
	final public function authenticated() {
		if(!isset($_COOKIE['lorry_session']) && !isset($_COOKIE['lorry_login']))
			return false;
		$this->ensureUser();
		return $this->user !== false;
	}

	/**
	 * 
	 * @return string
	 */
	final protected function castState() {
		$state = bin2hex(openssl_random_pseudo_bytes(16));
		return $state;
	}

	final protected function ensureState() {
		$this->ensureSession();
		if(!isset($_SESSION['state']) || !$_SESSION['state']) {
			$this->regenerateState();
		}
	}

	/**
	 * 
	 * @return string
	 */
	final public function getState() {
		$this->ensureState();
		return $_SESSION['state'];
	}

	/**
	 * 
	 * @return string
	 */
	final public function regenerateState() {
		$this->ensureSession();
		$state = $this->castState();
		$_SESSION['state'] = $state;
		return $state;
	}

	/**
	 * 
	 * @param string $state
	 * @return bool
	 */
	final public function verifyState($state) {
		$this->ensureSession();
		if(!isset($_SESSION['state']) || !is_string($state)) {
			return false;
		}
		if(hash_equals($state, $_SESSION['state'])) {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * @param string $state
	 */
	final public function setAuthorizationState($state) {
		$this->ensureSession();
		$_SESSION['authorization_state'] = $state;
	}

	/**
	 * 
	 * @param string $state
	 * @return bool
	 */
	final public function verifyAuthorizationState($state) {
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

	final public function clearAuthorizationState() {
		$this->ensureSession();
		unset($_SESSION['authorization_state']);
	}

	/**
	 *
	 * @return \Lorry\Model\User
	 * @throws Exception
	 */
	final public function getUser() {
		if(!$this->authenticated()) {
			throw new Exception('session is not authenticated');
		}
		$this->ensureUser();
		return $this->user;
	}

	/**
	 * Ensures that a possibly availabe user is loaded.
	 */
	final protected function ensureUser() {
		$this->ensureSession();
		if($this->user) {
			return;
		}
		if(isset($_SESSION['user']) && is_numeric($_SESSION['user'])) {
			$user = $this->persistence->build('User')->byId($_SESSION['user']);
			if($user && $user->matchSecret($_SESSION['secret'])) {
				$this->user = $user;
			} else {
				$this->logout();
			}
		} else if(isset($_COOKIE['lorry_login'])) {
			$user = false;
			$login = explode('$', $_COOKIE['lorry_login']);
			if(count($login) == 3 && is_numeric($login[1])) {
				$user = $this->persistence->build('User')->byId($login[1]);
				if($user && !empty($login[2]) && $user->matchSecret($login[2])) {
					$this->authenticate($user);
				}
			}
			if($this->user === false) {
				$this->logout();
			}
		}
		return;
	}

	/**
	 * 
	 * @param \Lorry\Model\User $user
	 */
	final protected function ensureSecret(User $user) {
		if(empty($user->getSecret())) {
			$user->regenerateSecret();
			$user->save();
		}
	}

	final public function ensureSession() {
		if(!$this->started) {
			session_start();
			$this->started = true;
		}
	}

	final public function logout() {
		$this->forget();
		$this->end();
	}

	final public function forget() {
		setcookie('lorry_login', '', 0, '/');
	}

	final public function end() {
		if(session_status() == PHP_SESSION_ACTIVE) {
			session_unset();
			session_destroy();
		}
		setcookie('lorry_session', '', 0, '/');
	}

	private static $OAUTH_PROVIDERS = array('openid', 'google', 'facebook');

	final public function handleOauth() {
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

	final public function authorizeResetPassword() {
		$_SESSION['password_reset_token'] = time();
	}

	final public function canResetPassword() {
		if(isset($_SESSION['password_reset_token'])) {
			// password reset is only valid for 5 minutes
			if($_SESSION['password_reset_token'] > time() - 5 * 60) {
				return true;
			} else {
				$this->clearPasswordReset();
			}
		}
		return false;
	}

	final public function clearResetPassword() {
		$_SESSION['password_reset_token'] = 0;
		unset($_SESSION['password_reset_token']);
	}

	protected $flags = array();

	final protected function getFlagName($flag) {
		return 'lorry_flag_'.$flag;
		;
	}

	final public function setFlag($flag, $persistent = false) {
		$time = 0;
		if($persistent) {
			$time = time() + 60 * 60 * 24 * 365;
		}
		$this->flags[$flag] = true;
		setcookie($this->getFlagName($flag), '1', $time, '/');
	}

	final public function unsetFlag($flag) {
		$this->flags[$flag] = false;
		setcookie($this->getFlagName($flag), '', 0, '/');
	}

	final public function getFlag($flag) {
		if(isset($this->flags[$flag]) && $this->flags[$flag] === true) {
			return true;
		}
		$name = $this->getFlagName($flag);
		return isset($_COOKIE[$name]) && filter_input(INPUT_COOKIE, $name) === '1';
	}

}
