<?php

namespace Lorry;

use \Lorry\Service\ConfigService;
use \Twig_Environment;

class Presenter implements PresenterInterface {

	/**
	 *
	 * @var \Lorry\Config
	 */
	protected $config;

	public function setConfig(ConfigService $config) {
		$this->config = $config;
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