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
}