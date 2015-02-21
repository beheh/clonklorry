<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Lorry\Service\MailService;
use Lorry\Service\JobService;
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
	 * @var \Lorry\Service\MailService
	 */
	private static $mail;

	public static function setMailService(MailService $mail) {
		self::$mail = $mail;
	}

	/**
	 *
	 * @var \Lorry\Service\JobService
	 */
	private static $job;

	public static function setJobService(JobService $job) {
		self::$job = $job;
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
		if(!self::valid($presenter)) {
			throw new Exception('unknown presenter "'.$presenter.'"');
		}
		$class = '\\Lorry\\Presenter\\'.$presenter;
		$instance = new $class();
		if(!$instance instanceof Presenter) {
			throw new Exception('presenter "'.$presenter.'" does not implement base class');
		}
		$instance->setConfigService(self::$config);
		$instance->setLocalisationService(self::$localisation);
		$instance->setSessionService(self::$session);
		$instance->setSecurityService(self::$security);
		$instance->setMailService(self::$mail);
		$instance->setJobService(self::$job);
		$instance->setTwig(self::$twig);
		return $instance;
	}

	/**
	 * Validates the existence of the presenter
	 * @param string $presenter
	 * @return bool True, if the presenter exists
	 */
	public static function valid($presenter) {
		return class_exists('\\Lorry\\Presenter\\'.$presenter);
	}

}
