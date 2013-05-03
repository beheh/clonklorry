<?php

namespace Lorry;

use \Lorry\Service\ConfigService;
use \Lorry\Service\SessionService;
use \Twig_Environment;

class Presenter implements PresenterInterface {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	public function setConfig(ConfigService $config) {
		$this->config = $config;
	}

	/**
	 *
	 * @var \Lorry\Service\SessionService
	 */
	protected $session;

	public function setSession(SessionService $session) {
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