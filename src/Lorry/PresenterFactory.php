<?php

namespace Lorry;

use \Twig_Environment;
use \Exception;

class PresenterFactory {

	private static $twig;

	public static function setTwig(Twig_Environment $twig) {
		self::$twig = $twig;
	}

	private static $config;

	public static function setConfig(Config $config) {
		self::$config = $config;
	}

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