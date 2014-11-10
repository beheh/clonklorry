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

	public final function setUp() {
		$environment = new Environment();
	}

	abstract function perform();

	public function tearDown() {
		
	}

}
