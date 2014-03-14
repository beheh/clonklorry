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

	public function requireIdentification() {
		$this->requireLogin();
		if(!$this->session->identified()) {
			throw new ForbiddenException();
		}
	}

	public function requireModerator() {
		$user = $this->session->getUser();
		if(!$user || !$user->isModerator()) {
			throw new ForbiddenException();
		}
	}

	public function requireAdministrator() {
		$user = $this->session->getUser();
		if(!$user || !$user->isAdministrator()) {
			throw new ForbiddenException();
		}
	}

	public function requireValidState() {
		$this->requireLogin();
		$state = filter_input(INPUT_GET, 'state');
		if(!$state) {
			$state = filter_input(INPUT_POST, 'state');
		}
		if(!$this->session->verifyState($state)) {
			throw new ForbiddenException();
		}
	}

}
