<?php

namespace Lorry;

use Psr\Log\LoggerInterface;
use Interop\Container\ContainerInterface;
use Lorry\Exception\OutputCompleteException;
use Lorry\Router;
use Symfony\Component\HttpFoundation\Request;

/**
 * @param \Lorry\Service\ConfigService $config
 * @param \Lorry\Service\PersistenceService $persistence
 * @param \Lorry\Service\LocalisationService $localisation
 * @param \Lorry\Service\MailService $mail
 * @param \Lorry\Service\JobService $job
 * @param \Lorry\Service\SessionService $session
 * @param \Lorry\Service\SecurityService $security
 * @param \Lorry\Service\CdnService $cdn
 * @param \Lorry\Router $router
 * @param \Lorry\TemplateEngineInterface $twig
 */
abstract class Presenter {

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

    /**
     *
     * @var \Interop\Container\ContainerInterface
     */
    private $container;


	public function __construct(LoggerInterface $logger, ContainerInterface $container) {
		$this->logger = $logger;
        $this->container = $container;
	}

    public function __get($name) {
        if($this->container->has($name)) {
            return $this->container->get($name);
        }
    }

    /**
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function setRequest(Request $request) {
        $this->request = $request;
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

	final protected function info($selector, $message) {
		$this->alert($selector, $message, 'info');
	}

	final protected function warning($selector, $message) {
		$this->alert($selector, $message, 'warning');
	}

	final protected function error($selector, $message) {
		$this->alert($selector, $message, 'danger');
	}

	final protected function success($selector, $message) {
		$this->alert($selector, $message, 'success');
	}

	final protected function hasAlert($selector) {
		return isset($this->context['alerts'][$selector]);
	}

	private function alert($selector, $message, $type) {
		if(!array_key_exists('alerts', $this->context)) {
			$this->context['alerts'] = array();
		}
		$this->context['alerts'][$selector] = array('message' => $message, 'type' => $type);
	}

	/**
	 * Sends a 301 Moved Permanently redirect.
	 * @param string $location
	 */
	final protected function redirect($location, $absolute = false) {
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
	final protected function reload() {
		return $this->redirect(Router::getPath());
	}

	/**
	 * Offers the user to identify his session.
	 */
	final protected function offerIdentification() {
		if(!$this->session->authenticated() || $this->session->identified()) {
			return;
		}
		$user = $this->session->getUser();

		if(isset($_POST['return'])) {
			$this->context['return'] = filter_input(INPUT_POST, 'return');
		} else {
            if($this->request) {
                $this->context['return'] = $this->request->getPathInfo();
            }
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
