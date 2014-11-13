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
	
	public function beforePerform() {
		
	}

	public final function setUp() {
		$environment = new Environment();
		$environment->setup();
		$this->config = $environment->getConfig();
		$this->mail = $environment->getMail();
		$this->persistence = $environment->getPersistence();
		$this->localisation = $environment->getLocalisation();
	}

	abstract function perform();

	public function tearDown() {
		
	}

}
