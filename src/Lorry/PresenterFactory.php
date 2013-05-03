<?php

namespace Lorry;

use \Lorry\Service\ConfigService;
use \Lorry\Service\SessionService;
use \Twig_Environment;
use \Exception;

class PresenterFactory {

	/**
	 *
	 * @var \Lorry\Config
	 */
	private static $config;

	public static function setConfig(ConfigService $config) {
		self::$config = $config;
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
	 * @var \Lorry\Session
	 */
	private static $session;

	public static function setSession(SessionService $session) {
		self::$session = $session;
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
		$instance->setTwig(self::$twig);
		$instance->setConfig(self::$config);
		return $instance;
	}

}