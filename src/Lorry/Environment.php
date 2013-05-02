<?php

namespace Lorry;

class Environment {

	public function __construct() {

	}

	public function handle() {
		$router = new Router($this);

		// preset twig variables for the template
		$twig = new \Twig_Environment();
		/*$twig->addGlobal('name', $this->config->name);
		$twig->addGlobal('base', $this->config->base);
		$twig->addGlobal('path', $router->getRequestedPath());
		$twig->addGlobal('current_year', date('Y'));
		$twig->addGlobal('__trademark', '<a class="text" href="http://clonk.de">' . gettext('"Clonk" is a registered trademark of Matthes Bender') . '</a>');
		if($this->config->debug) {
			$this->twig->addGlobal('__notice', gettext('Development version.'));
		}
		$session = $this->session->authenticated();
		$this->twig->addGlobal('__session', $session);
		if($session) {
			$user = $this->session->getUser();
			$this->twig->addGlobal('__username', $user->getUsername());
			$this->twig->addGlobal('__profile', $this->config->base . 'user/' . $user->getUsername());
			$this->twig->addGlobal('__administrator', $user->isAdministrator());
			$this->twig->addGlobal('__moderator', $user->isModerator());
		}*/
		$config = new Config();
		PresenterFactory::setConfig($config);
		PresenterFactory::setTwig($twig);

		$router->setRoutes(array(
			'/' => 'Site\Front',
			'/addons' => 'Addon\List',
			'/addons/:alpha' => 'Addon\Overview',
			'/addons/:alpha/:version' => 'Addon\Release',
			'/download' => 'Redirect\Front',
			'/download/:alpha' => 'Addon\Download',
			'/download/:alpha/:version' => 'Addon\Download',
			'/publish' => 'Publish\List',
			'/publish/:alpha' => 'Publish\Addon',
			'/publish/:alpha/:version' => 'Publish\Release',
			'/publish/:alpha/:version/preview' => 'Addon\Overview',
			'/moderate/approve' => '',
			'/moderate/approve/:alpha' => '',
			'/users/' => 'User\List',
			'/users/:alpha' => 'User\Profile',
			'/register' => 'Account\Register',
			'/login' => 'Account\Login',
			'/logout' => 'Account\Logout',
			'/settings' => 'Accout\Settings',
			'/about' => 'Site\About',
			'/clonk' => 'Site\Clonk',
			'/community' => 'Site\Community',
			'/contact' => 'Site\Contact'
		));

		$presenter = $router->route();

		$method = strtolower($_SERVER['REQUEST_METHOD']);
		if(!method_exists($presenter, $method)) {
			header('HTTP/1.1 501 Not Implemented');
			throw new \Exception('method not supported.');
		}
		call_user_func_array(array($presenter, $method), $router->getMatches());

		/*if($presenter) {
			try {
				//render out the presenter
				$rendered = $presenter->display();
				if($rendered !== true) {
					if(!empty($rendered)) {
						echo $rendered;
					} else {
						if($this->config->debug) {
							$error = $router->custom('error/debug');
							$error->setTitle('No output from presenter "' . $presenter . '"');
							$error->setMessage('<p>The presenter processed okay, but didn\'t return any result.</p>');
						} else {
							$error = $router->custom('error/internal');
						}
						echo $error->display();
					}
				}
			} catch(Exception $ex) {
				if($this->config->debug) {
					$error = $router->custom('error/debug');
					$error->setTitle('Uncaught Exception in presenter "' . $presenter . '"');
					$error->setDetails($ex);
				} else {
					$error = $router->custom('error/internal');
				}
				echo $error->display();
			}
		} else {
			throw new \Exception('fatal error: router didn\'t return presenter');
		}*/
	}
}