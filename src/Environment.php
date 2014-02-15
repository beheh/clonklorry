<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\LocalisationService;
use Lorry\Service\PersistenceService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Lorry\Service\StyleService;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\NotImplementedException;
use Lorry\Exception\OutputCompleteException;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Exception as PHPException;

class Environment {

	/**
	 * Handle an HTTP request.
	 */
	public function requestHandle() {
		$config = new ConfigService();

		try {
			$this->handle($config);
		} catch(PHPException $e) {
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-Type: text/plain');
			if($config && $config->get('debug')) {
				echo 'An internal error occured: '.get_class($e).PHP_EOL.PHP_EOL;
				if($e->getMessage()) {
					echo 'Message: '.$e->getMessage().PHP_EOL.PHP_EOL;
				}
				echo 'Stack trace:'.PHP_EOL.$e->getTraceAsString().PHP_EOL.PHP_EOL;
				echo 'Lorry platform in debug mode.';
			} else {
				echo 'An internal error occured.'.PHP_EOL;
				echo 'We have been notified and will be looking into this.';
			}
		}
	}

	protected function handle(ConfigService $config) {

		$persistence = new PersistenceService();
		$persistence->setConfigService($config);

		ModelFactory::setConfigService($config);
		ModelFactory::setPersistenceService($persistence);

		$session = new SessionService();

		$localisation = new LocalisationService();
		$localisation->setSessionService($session);
		$localisation->localize();

		$loader = new Twig_Loader_Filesystem('../app/templates');
		$twig = new Twig_Environment($loader, array('cache' => '../app/cache/twig', 'debug' => $config->get('debug')));
		$twig->addExtension(new \Twig_Extension_Escaper(true));
		$twig->addExtension(new \Twig_Extensions_Extension_I18n());

		$twig->addGlobal('brand', htmlspecialchars($config->get('brand')));
		$twig->addGlobal('base', htmlspecialchars($config->get('base')));
		$twig->addGlobal('path', explode('/', trim(Router::getPath(), '/')));
		$twig->addGlobal('filename', htmlspecialchars(rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')));

		//$twig->addGlobal('site_notice', '');
		$twig->addGlobal('site_copyright', htmlspecialchars('© '.date('Y')));
		$twig->addGlobal('site_trademark', '<a class="text" href="http://clonk.de">'.gettext('"Clonk" is a registered trademark of Matthes Bender').'</a>');
		$twig->addGlobal('site_contact', $config->get('contact'));

		if($session->authenticated()) {
			$user = $session->getUser();
			$twig->addGlobal('user_login', true);
			$twig->addGlobal('user_name', $user->getUsername());
			$twig->addGlobal('user_profile', $config->get('base').'/users/'.$user->getUsername());
			$twig->addGlobal('user_administrator', $user->isAdministrator());
			$twig->addGlobal('user_moderator', $user->isModerator());
		}

		$security = new SecurityService();
		$security->setSessionService($session);

		PresenterFactory::setConfigService($config);
		PresenterFactory::setLocalisationService($localisation);
		PresenterFactory::setSecurityService($security);
		PresenterFactory::setSessionService($session);
		PresenterFactory::setTwig($twig);

		Router::setRoutes(array(
			'/' => 'Site\Front',
			'/addons' => 'Addon\Portal',
			'/addons/:alpha' => 'Addon\Game',
			'/addons/:alpha/:alpha' => 'Addon\Release',
			'/addons/:alpha/:alpha/:version' => 'Addon\Release',
			'/download/' => 'Redirect\Front',
			'/download/:alpha/:alpha' => 'Addon\Download',
			'/download/:alpha/:alpha/:version' => 'Addon\Download',
			'/publish' => 'Publish\Portal',
			'/publish/:alpha' => 'Publish\Game',
			'/publish/:alpha/:alpha' => 'Publish\Addon',
			'/publish/:alpha/:alpha/:version' => 'Publish\Release',
			'/users' => 'User\Table',
			'/users/:alpha' => 'User\Profile',
			'/admin' => 'Manage\Administration',
			'/moderate' => 'Manage\Moderation',
			'/register' => 'Account\Register',
			'/login' => 'Account\Login',
			'/logout' => 'Account\Logout',
			'/settings' => 'Account\Settings',
			'/auth/gateway/openid' => 'Auth\Gateway',
			'/auth/gateway/openid' => 'Auth\Gateway',
			'/auth/gateway/facebook' => 'Auth\Gateway',
			'/auth/gateway/facebook/int_callback' => 'Auth\Gateway',
			'/auth/gateway/google' => 'Auth\Gateway',
			'/auth/gateway/google/oauth2callback' => 'Auth\Gateway',
			'/auth/callback' => 'Auth\Callback',
			'/about' => 'Site\About',
			'/clonk' => 'Site\Clonk',
			'/community' => 'Site\Community',
			'/contact' => 'Site\Contact',
			'/error/forbidden' => 'Error\Forbidden',
			'/error/notfound' => 'Error\NotFound',
		));

		// determine the RESTful method
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		// recompile style if necessary
		$style = new StyleService();
		$style->setConfigService($config);
		$style->compile();

		try {
			// determine the controller
			$presenter = Router::route();

			// check if method is supported
			if(!method_exists($presenter, $method)) {
				throw new NotImplementedException(get_class($presenter).'->'.$method.'()');
			}

			// execute the RESTful method
			return call_user_func_array(array($presenter, $method), Router::getMatches());
		} catch(Exception $exception) {
			$presenter = $exception->getPresenter();
			if(PresenterFactory::valid($presenter)) {
				return PresenterFactory::build($exception->getPresenter())->get($exception);
			}
		}
	}

}
