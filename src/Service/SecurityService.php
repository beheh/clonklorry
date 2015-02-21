<?php

namespace Lorry\Service;

use Lorry\Service\ConfigService;
use Lorry\Service\SessionService;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception;
use Lorry\Model\User;

class SecurityService {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	/**
	 *
	 * @var \Lorry\Service\SessionService
	 */
	protected $session;

	public function setSessionService(SessionService $session) {
		$this->session = $session;
	}

	public function requireLogin() {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException('requires login');
		}
	}

	/**
	 * 
	 * @throws \Lorry\Exception\ForbiddenException
	 */
	public function requireIdentification() {
		$this->requireLogin();
		if(!$this->session->identified()) {
			throw new ForbiddenException('requires identification');
		}
	}

	/**
	 * 
	 * @throws \Lorry\Exception\ForbiddenException
	 */
	public function requireModerator() {
		$this->requireLogin();
		$user = $this->session->getUser();
		if(!$user || (!$user->isModerator() && !$user->isAdministrator())) {
			throw new ForbiddenException('requires moderator');
		}
	}

	/**
	 * 
	 * @throws \Lorry\Exception\ForbiddenException
	 */
	public function requireAdministrator() {
		$this->requireLogin();
		$user = $this->session->getUser();
		if(!$user || !$user->isAdministrator()) {
			throw new ForbiddenException('requires administrator');
		}
	}

	/**
	 * 
	 * @throws \Lorry\Exception\ForbiddenException
	 */
	public function requireValidState() {
		$this->requireLogin();
		$state = filter_input(INPUT_GET, 'state');
		if(!$state) {
			$state = filter_input(INPUT_POST, 'state');
		}
		if(!$this->session->verifyState($state)) {
			throw new ForbiddenException('no valid state');
		}
	}

	/**
	 * 
	 * @throws \Lorry\Exception\ForbiddenException
	 */
	public function requireUploadRights() {
		$this->requireLogin();
		$user = $this->session->getUser();
		if(!$this->config->get('enable/upload')) {
			throw new ForbiddenException(gettext('uploading files is disabled'));
		}
		if(!$user->isActivated()) {
			throw new ForbiddenException(gettext('activate your account to add files'));
		}
		/* if($user->uploadedFiles() > 5) {
		  throw new ForbiddenException(gettext('you have too many unreleased files'));
		  } */
	}

	public function signActivation(User $user, $expires, $address) {
		if(!$user || !$expires || !$address) {
			throw new Exception('incomplete activation signing request');
		}
		return $this->sign($user->getId().':'.intval($expires).':'.$address);
	}
	
	public function signLogin(User $user, $expires, $counter, $reset = false) {
		if(!$user || !$expires || !$counter) {
			throw new Exception('incomplete login signing request');
		}
		return $this->sign($user->getId().':'.intval($expires).':'.intval($counter).':'.intval($reset));
	}

	public function sign($data) {
		$algo = $this->config->get('tokens/algorithm');
		$key = $this->config->get('tokens/key');
		if(!$algo || !$key || !$data) {
			throw new Exception('missing token signature algorithm or key');
		}
		return hash_hmac($algo, $data, $key);
	}

}
