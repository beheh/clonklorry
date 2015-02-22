<?php

namespace Lorry;

use Psr\Log\LoggerInterface;
use Lorry\Service\ConfigService;
use Lorry\Service\PersistenceService;
use Lorry\Service\LocalisationService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Lorry\Service\MailService;
use Lorry\Service\JobService;
use Lorry\Exception\OutputCompleteException;
use Lorry\Router;
use Twig_Environment;

abstract class Presenter {

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 *
	 * @var \Lorry\Service\ConfigService
	 */
	protected $config;

	/**
	 *
	 * @var \Lorry\Service\PersistenceService
	 */
	protected $persistence;

	/**
	 *
	 * @var \Lorry\Service\LocalisationService
	 */
	protected $localisation;

	/**
	 *
	 * @var \Lorry\Service\SecurityService
	 */
	protected $security;

	/**
	 *
	 * @var \Lorry\Service\SessionService
	 */
	protected $session;

	/**
	 *
	 * @var \Lorry\Service\MailService
	 */
	protected $mail;

	/**
	 *
	 * @var \Lorry\Service\JobService
	 */
	protected $job;

	/**
	 *
	 * @var \Twig_Environment;
	 */
	protected $twig;

	public function __construct(LoggerInterface $logger, ConfigService $config, PersistenceService $persistence, LocalisationService $localisation, SecurityService $security, SessionService $session, MailService $mail, JobService $job, Twig_Environment $template) {
		$this->logger = $logger;
		$this->config = $config;
		$this->persistence = $persistence;
		$this->localisation = $localisation;
		$this->security = $security;
		$this->session = $session;
		$this->mail = $mail;
		$this->job = $job;
		$this->twig = $template;
	}

	/**
	 * Handle the RESTful method
	 */
	public function handle($method, $parameters) {
		return call_user_func_array(array($this, $method), $parameters);
	}

	/**
	 * Default handling for post requests.
	 */
	public function post() {
		return call_user_func_array(array($this, 'get'), func_get_args());
	}

	protected $context = array();

	protected function display($template) {
		$this->twig->display($template, $this->context);
	}

	protected final function info($selector, $message) {
		$this->alert($selector, $message, 'info');
	}

	protected final function warning($selector, $message) {
		$this->alert($selector, $message, 'warning');
	}

	protected final function error($selector, $message) {
		$this->alert($selector, $message, 'danger');
	}

	protected final function success($selector, $message) {
		$this->alert($selector, $message, 'success');
	}

	protected final function hasAlert($selector) {
		return isset($this->context['alerts'][$selector]);
	}

	private final function alert($selector, $message, $type) {
		if(!array_key_exists('alerts', $this->context)) {
			$this->context['alerts'] = array();
		}
		$this->context['alerts'][$selector] = array('message' => $message, 'type' => $type);
	}

	/**
	 * Sends a 301 Moved Permanently redirect.
	 * @param string $location
	 */
	protected final function redirect($location, $absolute = false) {
		// @todo stricter filtering, remove newlines
		if(!$absolute) {
			$location = $this->config->get('base').$location;
		}
		// we use 303 here to hinder POST attacks on pages with returnto parameter
		header('HTTP/1.1 303 See Other');
		header('Location: '.$location);
		throw new OutputCompleteException;
	}

	/**
	 * Sends a 301 Moved Permanently redirect to the current url.
	 */
	protected final function reload() {
		return $this->redirect(Router::getPath());
	}

	/**
	 * Offers the user to identify his session.
	 */
	protected final function offerIdentification() {
		if(!$this->session->authenticated() || $this->session->identified()) {
			return;
		}
		$user = $this->session->getUser();

		if(isset($_POST['return'])) {
			$this->context['return'] = filter_input(INPUT_POST, 'return');
		} else {
			// @todo
			//$this->context['return'] = $request->getPathInfo();
		}

		$this->context['password'] = false;
		if($user->hasPassword()) {
			$this->context['password'] = true;
			if(isset($_POST['password'])) {
				$this->error('identify', gettext('Password wrong.'));
			}
		}
		$this->display('account/identify.twig');
		throw new OutputCompleteException;
	}

}
