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
	
	public function beforePerform() {
		
	}

	public final function setUp() {
		$environment = new Environment();
		$environment->setup();
		$this->config = $environment->config;
		$this->mail = $environment->mail;
		$this->persistence = $environment->persistence;
	}

	abstract function perform();

	public function tearDown() {
		
	}

}
