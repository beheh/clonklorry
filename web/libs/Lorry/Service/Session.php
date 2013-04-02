<?php

class Lorry_Service_Session extends Lorry_Service {

	protected $started = false;
	protected $user = false;

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		session_name('lorrysession');
	}

	public final function start(Lorry_Model_User $user, $remember) {
		$this->ensureSession();
		session_regenerate_id();
		if(!$user->isLoaded()) {
			throw new Exception('user is not loaded or does not exist');
		}
		$_SESSION['user'] = $user->getId();
	}

	public final function authenticated() {
		if(!isset($_COOKIE['lorrysession']))
			return false;
		$this->ensureUser();
		return $this->user !== false;
	}

	public final function getUser() {
		if(!$this->authenticated()) {
			throw new Exception('session is not authenticated');
		}
		$this->ensureUser();
		return $this->user;
	}

	protected final function ensureUser() {
		$this->ensureSession();
		if(isset($_SESSION['user']) && is_numeric($_SESSION['user'])) {
			$user_id = $_SESSION['user'];
			$this->user = $this->lorry->persistence->get('user')->byId($user_id);
		}
	}

	protected final function ensureSession() {
		if(!$this->started) {
			session_start();
			$this->started = true;
		}
		return true;
	}

	public final function end() {
		session_unset();
		session_destroy();
		setcookie('lorrysession', null, 0);
	}

}

