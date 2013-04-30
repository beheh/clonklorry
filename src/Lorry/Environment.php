<?php

namespace Lorry;

class Environment {

	public function __construct() {

	}

	public function handle() {
		header_remove('X-Powered-By');
		$router = new Router($this);

		//cast the view based on the request
		$view = $router->route();

		$this->twig->addGlobal('name', $this->config->name);
		$this->twig->addGlobal('base', $this->config->base);
		$this->twig->addGlobal('path', $router->getRequestedPath());
		$this->twig->addGlobal('current_year', date('Y'));
		$this->twig->addGlobal('__trademark', '<a class="text" href="http://clonk.de">'.gettext('"Clonk" is a registered trademark of Matthes Bender').'</a>');
		if($this->config->debug) {
			$this->twig->addGlobal('__notice', gettext('Development version.'));
		}
		$session = $this->session->authenticated();
		$this->twig->addGlobal('__session', $session);
		if($session) {
			$user = $this->session->getUser();
			$this->twig->addGlobal('__username', $user->getUsername());
			$this->twig->addGlobal('__profile', $this->config->base.'user/'.$user->getUsername());
			$this->twig->addGlobal('__administrator', $user->isAdministrator());
			$this->twig->addGlobal('__moderator', $user->isModerator());
		}

		if($view) {
			try {
				//render out the view
				$rendered = $view->display();
				if($rendered !== true) {
					if(!empty($rendered)) {
						echo $rendered;
					} else {
						if($this->config->debug) {
							$error = $router->custom('error/debug');
							$error->setTitle('No output from view "' . $view . '"');
							$error->setMessage('<p>The view processed okay, but didn\'t return any result.</p>');
						} else {
							$error = $router->custom('error/internal');
						}
						echo $error->display();
					}
				}
			} catch(Exception $ex) {
				if($this->config->debug) {
					$error = $router->custom('error/debug');
					$error->setTitle('Uncaught Exception in view "' . $view . '"');
					$error->setDetails($ex);
				} else {
					$error = $router->custom('error/internal');
				}
				echo $error->display();
			}
		} else {
			throw new Exception('fatal error: router didn\'t return view');
		}
	}

	private $services = array();

	/**
	 * @param $name string Class name of the service
	 * @return Lorry_Service The service
	 */
	public function __get($name) {
		$class_name = '\\Lorry\\Service\\' . ucfirst($name);
		if(!isset($this->services[$name])) {
			if(class_exists($class_name)) {
				$this->services[$name] = new $class_name($this);
			} else {
				throw new \UnexpectedValueException('unknown service ' . $class_name);
			}
		}
		return $this->services[$name];
	}

	public function getRootDir() {
		return realpath(__DIR__ . '/../') . '/';
	}

	public function getVersion() {
		return 'dev';
	}

}