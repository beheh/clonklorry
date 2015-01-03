<?php

namespace Lorry;

use Analog\Analog;
use Lorry\Exception\NotImplementedException;
use Twig_Loader_Filesystem;
use Twig_Environment;

class Environment {

	/**
	 * @var \Lorry\Service\ConfigService;
	 */
	private $config;

	/**
	 * @var \Lorry\Service\PersistenceService;
	 */
	private $persistence;

	/**
	 * @var \Lorry\Service\LocalisationService;
	 */
	private $localisation;

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * @var \Lorry\Service\SecurityService;
	 */
	private $security;

	/**
	 * @var \Lorry\Service\SecurityService;
	 */
	private $mail;

	/**
	 * @var \Lorry\Service\JobService;
	 */
	private $job;
	
	/**
	 * @var 
	 */

	public function setup() {
		$config = new Service\ConfigService();
		$loglevel = $config->get('debug') ? \Analog::DEBUG : \Analog::INFO;
		\Analog::handler(\Analog\Handler\Threshold::init(
						\Analog\Handler\File::init(__DIR__.'/../app/logs/lorry.log'), $loglevel
		));
		$this->config = $config;

		// persistence
		$persistence = new Service\PersistenceService();
		$persistence->setConfigService($config);
		ModelFactory::setConfigService($config);
		ModelFactory::setPersistenceService($persistence);
		$this->persistence = $persistence;

		// localisation
		$localisation = new Service\LocalisationService();
		$this->localisation = $localisation;

		// templating
		$loader = new Twig_Loader_Filesystem(__DIR__.'/../app/templates');
		$twig = new Twig_Environment($loader, array('cache' => __DIR__.'/../app/cache/twig', 'debug' => $config->get('debug')));
		$twig->addExtension(new \Twig_Extension_Escaper(true));
		$twig->addExtension(new \Twig_Extensions_Extension_I18n());
		$twig->addGlobal('brand', htmlspecialchars($config->get('brand')));
		$twig->addGlobal('base', htmlspecialchars($config->get('base')));
		$twig->addGlobal('resources', htmlspecialchars($config->get('base').'/resources'));
		$twig->addGlobal('site_copyright', htmlspecialchars('© '.date('Y')));
		$twig->addGlobal('site_trademark', '<a class="text" href="http://clonk.de">'.gettext('"Clonk" is a registered trademark of Matthes Bender').'</a>');
		$twig->addGlobal('site_enabled', $config->get('enable/site'));
		$twig->addGlobal('site_notice', $config->get('notice/text'));
		$twig->addGlobal('site_notice_class', $config->get('notice/class'));
		$twig->addGlobal('site_tracking', $config->getTracking());
		$twig->addGlobal('enable', array('upload' => $config->get('enable/upload')));
		
		$this->twig = $twig;

		// security
		$security = new Service\SecurityService();
		$security->setConfigService($config);
		$this->security = $security;

		// mail
		$mail = new Service\MailService();
		$mail->setConfigService($config);
		$mail->setLocalisationService($localisation);
		EmailFactory::setConfigService($config);
		EmailFactory::setLocalisationService($localisation);
		EmailFactory::setSecurityService($security);
		EmailFactory::setTwig($twig);
		$this->mail = $mail;

		// jobs
		$job = new Service\JobService();
		$job->setConfigService($config);
		$this->job = $job;
		
		// content delivery
		$cdn = new Service\CdnService();
		$cdn->setConfigService($config);
		$this->cdn = $cdn;
	}

	public function handle() {
		$config = $this->config;

		$session = new Service\SessionService();

		// localize depending on viewer
		$localisation = $this->localisation;
		$localisation->setSessionService($session);
		$localisation->localize();

		// security based on login
		$security = $this->security;
		$security->setSessionService($session);

		$twig = $this->twig;
		$twig->addGlobal('path', explode('/', trim(Router::getPath(), '/')));
		$twig->addGlobal('origpath', trim(Router::getPath()));
		$twig->addGlobal('filename', htmlspecialchars(rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')));
		$language = $localisation->getDisplayLanguage();
		$twig->addGlobal('locale', str_replace('-', '_', $language));
		$languages = $localisation->getAvailableLanguages();
		$twig->addGlobal('nextlocale', strstr($languages[(array_search($language, $languages) + 1) % count($languages)], '-', true));
		$twig->addGlobal('fbid', $config->get('oauth/facebook/id'));
		$twig->addGlobal('knows_clonk', $session->getFlag('knows_clonk'));

		if($session->authenticated()) {
			$user = $session->getUser();
			$twig->addGlobal('user_login', true);
			$twig->addGlobal('user_name', $user->getUsername());
			$twig->addGlobal('user_profile', $config->get('base').'/users/'.$user->getUsername());
			$twig->addGlobal('user_administrator', $user->isAdministrator());
			$twig->addGlobal('user_moderator', $user->isModerator());
			$twig->addGlobal('state', $session->getState());
		}

		$mail = $this->mail;
		$job = $this->job;
		PresenterFactory::setConfigService($config);
		PresenterFactory::setLocalisationService($localisation);
		PresenterFactory::setSecurityService($security);
		PresenterFactory::setSessionService($session);
		PresenterFactory::setMailService($mail);
		PresenterFactory::setJobService($job);
		PresenterFactory::setTwig($twig);

		// routing
		if($config->get('enable/site')) {
			Router::addRoutes(array(
				'/' => 'Site\Front',
				'/addons' => 'Addon\Portal',
				'/addons/:alpha' => 'Addon\Game',
				'/addons/:alpha\.json' => 'Addon\Api\Game',
				'/addons/:alpha/:alpha' => 'Addon\Release',
				'/addons/:alpha/:alpha\.json' => 'Addon\Api\Release',
				'/addons/:alpha/:alpha/:version' => 'Addon\Release',
				'/games\.json' => 'Addon\Api\Games',
				'/download' => 'Redirect\Front',
				'/download/:alpha/:alpha' => 'Addon\Download',
				'/download/:alpha/:alpha/:version' => 'Addon\Download',
				'/developers' => 'Publish\Developers',
				'/publish' => 'Publish\Portal',
				'/publish/create' => 'Publish\Create',
				'/publish/:number' => 'Publish\Edit',
				'/publish/:number/:version' => 'Publish\Release',
				'/publish/:number/:version/query' => 'Publish\Api\QueryFile',
				'/publish/:number/:version/remove' => 'Publish\Api\RemoveFile',
				'/publish/:number/:version/upload' => 'Publish\Api\UploadFile',
				'/users' => 'User\Table',
				'/users/:alpha' => 'User\Profile',
				'/users/:alpha/edit' => 'User\Edit',
				'/admin' => 'Manage\Administration',
				'/moderate' => 'Manage\Moderation',
				'/moderate/approve/:number' => 'Manage\Approve',				
				'/register' => 'Account\Register',
				'/login' => 'Account\Login',
				'/logout' => 'Account\Logout',
				'/settings' => 'Account\Settings',
				'/activate' => 'Account\Activate',
				'/identify' => 'Account\Identify',
				'/auth/gateway/:alpha' => 'Auth\Gateway',
				'/auth/callback/:alpha' => 'Auth\Callback',
				'/about' => 'Site\About',
				'/clonk' => 'Site\Clonk',
				'/api' => 'Site\Api',
				'/privacy' => 'Site\Privacy',
				'/contact' => 'Site\Contact',
				'/language' => 'Site\Language',
			));
		} else {
			Router::addRoutes(array(
				'/' => 'Site\Disabled'
			));
		}

		// debug routing
		if($config->get('debug')) {
			Router::addRoutes(array(
				'/debug/cachewarmer' => 'Debug\CacheWarmer',
				'/debug/jobsubmitter' => 'Debug\JobSubmitter'
			));
		}

		// determine the RESTful method
		$method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING));

		try {
			// determine the controller
			$presenter = Router::route();

			// check if method is supported
			if(!method_exists($presenter, $method) || ($method !== 'get' && $method !== 'post')) {
				throw new NotImplementedException(get_class($presenter).'->'.$method.'()');
			}

			// execute the RESTful method
			return $presenter->handle($method, Router::getMatches());
		} catch(Exception $exception) {
			$presenter = $exception->getPresenter();
			if(PresenterFactory::valid($presenter)) {
				return PresenterFactory::build($exception->getPresenter())->get($exception);
			}
		} catch(\Exception $exception) {
			return PresenterFactory::build('Error')->get($exception);
		}
	}

	public function getConfig() {
		return $this->config;
	}

	public function getPersistence() {
		return $this->persistence;
	}
	
	public function getLocalisation() {
		return $this->localisation;
	}

	public function getTemplating() {
		return $this->twig;
	}

	public function getSecurity() {
		return $this->security;
	}

	public function getMail() {
		return $this->mail;
	}

	public function getJob() {
		return $this->job;
	}
	
	public function getCdn() {
		return $this->cdn;
	}

}
