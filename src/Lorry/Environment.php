<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\PersistenceService;
use Lorry\Service\SecurityService;
use Lorry\Service\SessionService;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\NotImplementedException;

class Environment {

	public function handle() {
		$config = new ConfigService();

		$persistence = new PersistenceService();
		$persistence->setConfigService($config);

		ModelFactory::setPersistenceService($persistence);

		$session = new SessionService();

		$loader = new \Twig_Loader_Filesystem('../app/templates');
		$twig = new \Twig_Environment($loader, array('cache' => '../app/cache/twig', 'debug' => true));
		$twig->addExtension(new \Twig_Extension_Escaper(true));
		$twig->addExtension(new \Twig_Extensions_Extension_I18n());

		$twig->addGlobal('brand', $config->get('brand'));
		$twig->addGlobal('base', $config->get('base'));
		$twig->addGlobal('filename', basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
		$twig->addGlobal('site_notice', gettext('Development version.'));
		$twig->addGlobal('site_copyright', '© '.date('Y'));
		$twig->addGlobal('site_trademark', '<a class="text" href="http://clonk.de">'.gettext('"Clonk" is a registered trademark of Matthes Bender').'</a>');

		if($session->authenticated()) {
			$user = $session->getUser();
			$twig->addGlobal('user_login', true);
			$twig->addGlobal('user_name', $user->getUsername());
			$twig->addGlobal('user_profile', $config->get('base').'/users/'.$user->getUsername());
		}

		$security = new SecurityService();
		$security->setSessionService($session);

		PresenterFactory::setConfigService($config);
		PresenterFactory::setSecurityService($security);
		PresenterFactory::setSessionService($session);
		PresenterFactory::setTwig($twig);

		Router::setRoutes(array(
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
			'/users' => 'User\Table',
			'/users/:alpha' => 'User\Profile',
			'/register' => 'Account\Register',
			'/login' => 'Account\Login',
			'/logout' => 'Account\Logout',
			'/settings' => 'Account\Settings',
			'/about' => 'Site\About',
			'/clonk' => 'Site\Clonk',
			'/community' => 'Site\Community',
			'/contact' => 'Site\Contact'
		));

		// determine the RESTful method
		$method = strtolower($_SERVER['REQUEST_METHOD']);

		try {

			// determine the controller
			$presenter = Router::route();

			// check if method is supported
			if(!method_exists($presenter, $method)) {
				throw new NotImplementedException(get_class($presenter).'->'.$method.'()');
			}

			// execute the RESTful method
			call_user_func_array(array($presenter, $method), Router::getMatches());
		} catch(FileNotFoundException $exception) {
			return PresenterFactory::build('Error\FileNotFound')->get($exception);
		} catch(ForbiddenException $exception) {
			return PresenterFactory::build('Error\Forbidden')->get($exception);
		} catch(NotImplementedException $exception) {
			return PresenterFactory::build('Error\NotImplemented')->get($exception);
		}
	}

}