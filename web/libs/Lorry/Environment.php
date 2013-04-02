<?php

require_once 'Autoloader.php';

class Lorry_Environment {

	/**
	 * @var array contains all services
	 */
	private $services = array();

	public function __construct() {
		Lorry_Autoloader::register();
		set_exception_handler('Lorry_Environment::exceptionHandler');
		set_error_handler('Lorry_Environment::errorHandler');
	}

	/**
	 * Loads and executes the requested view and displays the output.
	 */
	public function handle() {
		header_remove('X-Powered-By');
		$router = new Lorry_Router($this);

		//cast the view based on the request
		$view = $router->route();
		if($view) {
			$context = array();
			$context['name'] = $this->config->name;
			$context['base'] = $this->config->baseUrl;
			$context['year'] = date('Y');
			if($this->config->debug['enabled']) {
				$context['notice'] = 'Development version.';
			}
			$context['nav'] = $router->getRequestedPath();
			$context['session'] = array('authenticated' => $this->session->authenticated());
			if($context['session']['authenticated']) {
				$user = $this->session->getUser();
				$context['session']['user'] = $user->getUsername();
			}
			$this->twig->setContext($context);
			try {
				//render out the view
				$rendered = $view->display();
				if($rendered !== true) {
					if(!empty($rendered)) {
						echo $rendered;
					} else {
						if($this->config->debug['enabled']) {
							$error = $router->custom('error/debug');
							$error->setTitle('No output from view "'.$view.'"');
							$error->setMessage('<p>The view processed okay, but didn\'t return any result.</p>');
						} else {
							$error = $router->custom('error/internal');
						}
						echo $error->display();
					}
				}
			} catch(Exception $ex) {
				if($this->config->debug['enabled']) {
					$error = $router->custom('error/debug');
					$error->setTitle('Uncaught Exception in view "'.$view.'"');
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

	static function exceptionHandler(Exception $exception) {
		echo($exception);
	}

	static function errorHandler($code, $message, $location) {
		//@TODO
		throw new Exception('Fatal Error: '.$message.' in '.$location);
	}

	/**
	 * @param $name string Class name of the service
	 * @return Lorry_Service The service
	 */
	public function __get($name) {
		$class_name = 'Lorry_Service_'.ucfirst($name);
		if(!isset($this->services[$name])) {
			if(class_exists($class_name)) {
				$this->services[$name] = new $class_name($this);
			} else {
				throw new UnexpectedValueException('unknown service '.$class_name);
			}
		}
		return $this->services[$name];
	}

}

