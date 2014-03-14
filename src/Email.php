<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Twig_Environment;

abstract class Email implements EmailInterface {

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	public function setConfigService(ConfigService $config) {
		$this->config = $config;
	}

	/**
	 *
	 * @var \Lorry\Service\LocalisationService
	 */
	protected $localisation;

	public function setLocalisationService(LocalisationService $localisation) {
		$this->localisation = $localisation;
	}

	/**
	 *
	 * @var \Lorry\Service\SecurityService
	 */
	protected $security;

	public function setSecurityService(SecurityService $security) {
		$this->security = $security;
	}

	/**
	 *
	 * @var \Lorry\Service\SessionService
	 */
	protected $session;

	public function setSessionService(SessionService $session) {
		$this->session = $session;
	}

	/**
	 *
	 * @var \Twig_Environment;
	 */
	private $twig;

	public function setTwig(Twig_Environment $twig) {
		$this->twig = $twig;
	}

	private $recipent;

	public function setRecipent($recipent) {
		$this->recipent = $recipent;
	}

	public function getRecipent() {
		return $this->recipent;
	}

	private $replyto;

	public function setReplyTo($replyto) {
		$this->replyto = $replyto;
	}

	public function getReplyTo() {
		return $this->replyto;
	}

	public function setUsername($username) {
		$this->context['username'] = $username;
	}

	protected $context = array();

	public abstract function write();

	private $subject;

	public function getSubject() {
		return $this->subject;
	}

	private $message;

	public function getMessage() {
		return $this->message;
	}

	protected function render($template) {
		$template = $this->twig->loadTemplate('email/'.$template);
		$this->subject = $template->renderBlock('subject', array_merge(array('brand' => $this->config->get('brand')), $this->context));
		$this->message = $template->render($this->context);
	}

}
