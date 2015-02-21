<?php

namespace Lorry;

abstract class Job {

	abstract static function getQueue();

	/**
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	/**
	 * @var \Lorry\Service\MailService
	 */
	protected $mail;

	/**
	 * @var \Lorry\Service\PersistenceService
	 */
	protected $persistence;

	/**
	 * @var \Lorry\Service\LocalisationService
	 */
	protected $localisation;

	/**
	 * @var \Twig_Environment
	 */
	protected $templating;

	/**
	 * @var \Lorry\Service\CdnService
	 */
	protected $cdn;

	/**
	 * @var \Lorry\Service\SecurityService
	 */
	protected $security;

	public final function setUp() {
		$environment = new Environment();
		$environment->setup();
		$this->config = $environment->getConfig();
		$this->mail = $environment->getMail();
		$this->persistence = $environment->getPersistence();
		$this->localisation = $environment->getLocalisation();
		$this->templating = $environment->getTemplating();
		$this->cdn = $environment->getCdn();
		$this->security = $environment->getSecurity();
	}

	abstract function perform();

	public function tearDown() {
		
	}

}
