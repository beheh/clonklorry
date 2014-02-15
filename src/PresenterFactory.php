<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Twig_Environment;
use Exception;

class PresenterFactory {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	private static $config;

	public static function setConfigService(ConfigService $config) {
		self::$config = $config;
	}

	/**
	 *
	 * @var \Lorry\Service\LocalisationService
	 */
	private static $localisation;

	public static function setLocalisationService(LocalisationService $localisation) {
		self::$localisation = $localisation;
	}

	/**
	 *
	 * @var \Lorry\Service\SecurityService
	 */
	private static $security;

	public static function setSecurityService(SecurityService $security) {
		self::$security = $security;
	}

	/**
	 *
	 * @var \Lorry\Service\SessionService
	 */
	private static $session;

	public static function setSessionService(SessionService $session) {
		self::$session = $session;
	}

	/**
	 *
	 * @var \Twig_Environment
	 */
	private static $twig;

	public static function setTwig(Twig_Environment $twig) {
		self::$twig = $twig;
	}

	/**
	 *
	 * @param string $presenter
	 * @return \Lorry\Presenter
	 * @throws Exception
	 */
	public static function build($presenter) {
		$class = '\\Lorry\\Presenter\\'.$presenter;
		if(!class_exists($class)) {
			throw new Exception('unknown presenter');
		}
		$instance = new $class();
		if(!$instance instanceof PresenterInterface) {
			throw new Exception('presenter does not implement interface');
		}
		$instance->setConfigService(self::$config);
		$instance->setLocalisationService(self::$localisation);
		$instance->setSessionService(self::$session);
		$instance->setSecurityService(self::$security);
		$instance->setTwig(self::$twig);
		return $instance;
	}

}