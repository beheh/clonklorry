<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Twig_Environment;

class Presenter implements PresenterInterface {

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
	 * @var \Lorry\Service\SecurityService
	 */
	protected $security;

	public function setSecurityService(SecurityService $security) {
		$this->security = $security;
	}

	/**
	 *
	 * @var \Lorry\Service\SessionService
	 */
	protected $session;

	public function setSessionService(SessionService $session) {
		$this->session = $session;
	}

	/**
	 *
	 * @var \Twig_Environment;
	 */
	protected $twig;

	public function setTwig(Twig_Environment $twig) {
		$this->twig = $twig;
	}

	/**
	 * Default handling for post requests.
	 */
	public function post() {
		call_user_func_array(array($this, 'get'), func_get_args());
	}

	/**
	 * Sends a 301 Moved Permanently redirect.
	 * @param string $location
	 */
	protected function redirect($location, $external = false) {
		if(!$external) {
			$location = $this->config->get('base').$location;
		}
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$location);
	}

}