<?php

namespace Lorry;

use Resque\AbstractJob;

/**
 * @param array $args
 */
abstract class Job extends AbstractJob {

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

	final public function setUp() {
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

	public function tearDown() {
		
	}

}
