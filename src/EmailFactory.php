<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Twig_Environment;
use Exception;

class EmailFactory {

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
	 * @var \Twig_Environment
	 */
	private static $twig;

	public static function setTwig(Twig_Environment $twig) {
		self::$twig = $twig;
	}

	/**
	 *
	 * @param string $email
	 * @return \Lorry\Email
	 * @throws Exception
	 */
	public static function build($email) {
		if(!self::valid($email)) {
			throw new Exception('unknown email');
		}
		$class = '\\Lorry\\Email\\'.$email;
		$instance = new $class();
		if(!$instance instanceof Email) {
			throw new Exception('email does not implement base class');
		}
		$instance->setConfigService(self::$config);
		$instance->setLocalisationService(self::$localisation);
		$instance->setSecurityService(self::$security);
		$instance->setTwig(self::$twig);
		return $instance;
	}

	/**
	 * Validates the existence of the email
	 * @param string $email
	 * @return bool True, if the email exists
	 */
	public static function valid($email) {
		return class_exists('\\Lorry\\Email\\'.$email);
	}

}
