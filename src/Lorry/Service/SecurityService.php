<?php

namespace Lorry\Service;

use Lorry\Service\SessionService;
use Lorry\Exception\ForbiddenException;

class SecurityService {

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
			throw new ForbiddenException();
		}
	}

	public function requireModerator() {
		$this->requireLogin();
		$user = $this->session->getUser();
		if(!$user || !$user->isModerator()) {
			throw new ForbiddenException();
		}
	}

	public function requireAdministrator() {
		$this->requireLogin();
		$user = $this->session->getUser();
		if(!$user || !$user->isAdministrator()) {
			throw new ForbiddenException();
		}
	}

}