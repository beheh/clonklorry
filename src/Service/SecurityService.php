<?php

namespace Lorry\Service;

use Lorry\Service\ConfigService;
use Lorry\Service\SessionService;
use Lorry\Exception\ForbiddenException;

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
			throw new ForbiddenException;
		}
	}

	public function requireIdentification() {
		$this->requireLogin();
		if(!$this->session->identified()) {
			throw new ForbiddenException;
		}
	}

	public function requireModerator() {
		$user = $this->session->getUser();
		if(!$user || !$user->isModerator()) {
			throw new ForbiddenException;
		}
	}

	public function requireAdministrator() {
		$user = $this->session->getUser();
		if(!$user || !$user->isAdministrator()) {
			throw new ForbiddenException;
		}
	}

	public function requireValidState() {
		$this->requireLogin();
		$state = filter_input(INPUT_GET, 'state');
		if(!$state) {
			$state = filter_input(INPUT_POST, 'state');
		}
		if(!$this->session->verifyState($state)) {
			throw new ForbiddenException;
		}
	}

	public function requireUploadRights() {
		$user = $this->session->getUser();
		if(!$this->config->get('enable/upload')) {
			throw new ForbiddenException(gettext('uploading files is disabled'));
		}
		if(!$user->isActivated()) {
			throw new ForbiddenException(gettext('activate your account to add files'));
		}
		/*if($user->uploadedFiles() > 5) {
			throw new ForbiddenException(gettext('you have too many unreleased files'));
		}*/
	}

}
