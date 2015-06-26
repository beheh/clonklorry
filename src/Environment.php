<?php

namespace Lorry;

use Lorry\Exception\NotImplementedException;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\Exception;
use Lorry\Service\ConfigService;
use Lorry\Logger\MonologLoggerFactory;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ApcCache;
use \Exception as PHPException;

class Environment
{
    /*
     * @var
     */
    const PROJECT_ROOT = __DIR__.'/..';

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    public function setup()
    {
        $loggerFactory = new MonologLoggerFactory();
        $this->logger = $loggerFactory->build(get_class($this));
        $this->logger->info('starting up');

        $config = new ConfigService($loggerFactory);

        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions(self::PROJECT_ROOT.'/app/definitions.php');
        $builder->useAnnotations(true);

        $cache = ($config->get('debug') || !function_exists('apc_store')) ? new ArrayCache() : new ApcCache();
        $cache->setNamespace($config->get('brand'));

        if (!$config->get('debug')) {
            $builder->setDefinitionCache($cache); //@todo fix dynamic caching
        }

        $container = $builder->build();
        $container->set(\Doctrine\Common\Cache\Cache::class, $cache);
        $container->set(\Lorry\Service\ConfigService::class, $config);
        $this->container = $container;

        error_reporting(E_ALL ^ E_STRICT);

        $container->set(\Interop\Container\ContainerInterface::class, $container);

        \Monolog\ErrorHandler::register($loggerFactory->build('errorHandler'));

        $templating = $container->get(\Lorry\TemplateEngineInterface::class);
        $templating->addGlobal('brand', htmlspecialchars($config->get('brand')));
        $templating->addGlobal('base', htmlspecialchars($config->get('base')));
        $templating->addGlobal('api', htmlspecialchars($config->get('base')).'/api');
        $templating->addGlobal('assets', htmlspecialchars($config->get('assets')));
        $templating->addGlobal('resources',
            htmlspecialchars($config->get('base').'/resources'));
        $templating->addGlobal('site_copyright',
            htmlspecialchars('© '.date('Y')));
        $templating->addGlobal('site_trademark',
            '<a class="text" href="http://clonk.de">'.gettext('&quot;Clonk&quot; is a registered trademark of Matthes Bender').'</a>');
        $templating->addGlobal('site_enabled', $config->get('enable/site'));
        $templating->addGlobal('site_notice', $config->get('notice/text'));
        $templating->addGlobal('site_notice_class', $config->get('notice/class'));
        $templating->addGlobal('site_tracking', $config->getTracking());
        $templating->addGlobal('enable',
            array('upload' => $config->get('enable/upload')));

        $this->logger->debug('startup complete');
    }

    public function handle()
    {
        try {
            $this->logger->info('handling request');
            $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

            $router = $this->container->get('router');
            $router->setPrefix('Lorry\Presenter');

            // localize depending on viewer
            $localisation = $this->container->get('localisation');
            $localisation->localize();

            $config = $this->container->get('config');

            $twig = $this->container->get(\Lorry\TemplateEngineInterface::class);
            $twig->addGlobal('path',
                explode('/', trim($request->getPathInfo(), '/')));
            $twig->addGlobal('origpath', trim($request->getPathInfo()));
            $twig->addGlobal('filename',
                htmlspecialchars(rtrim(parse_url($_SERVER['REQUEST_URI'],
                            PHP_URL_PATH), '/')));

            $displayLanguage = $localisation->getDisplayLanguage();
            $twig->addGlobal('locale', str_replace('-', '_', $displayLanguage));
            $twig->addGlobal('display_language', $displayLanguage);
            $twig->addGlobal('format_datetime', $localisation->getFormat(\Lorry\Service\LocalisationService::FORMAT_DATETIME));

            $twig->addGlobal('fbid', $config->get('oauth/facebook/id'));

            $session = $this->container->get('session');
            $twig->addGlobal('knows_clonk', $session->getFlag('knows_clonk'));

            if ($session->authenticated()) {
                $user = $session->getUser();
                $twig->addGlobal('user_login', true);
                $twig->addGlobal('user_name', $user->getUsername());
                $twig->addGlobal('user_profile',
                $config->get('base').'/users/'.$user->getUsername());
                $twig->addGlobal('user_administrator', $user->isAdministrator());
                $twig->addGlobal('user_moderator', $user->isModerator());
                $twig->addGlobal('state', $session->getState());
            }

            // routing
            if ($config->get('enable/site')) {
                // generic routes
                $router->addRoutes(array(
                    '/' => 'Site\Front',
                    '/addons' => 'Addon\Portal',
                    '/addons/:alpha' => 'Addon\Game',
                    '/addons/:alpha/:alpha' => 'Addon\Presentation',
                    '/addons/:alpha/:alpha/:version' => 'Addon\Presentation',
                    '/download' => 'Redirect\Front',
                    '/download/:alpha/:alpha' => 'Addon\Download',
                    '/download/:alpha/:alpha/:version' => 'Addon\Download',
                    '/developers' => 'Publish\Developers',
                    '/publish' => 'Publish\Portal',
                    '/publish/:number' => 'Publish\Edit',
                    '/publish/:number/:version' => 'Publish\Release',
                    '/users' => 'User\ListX',
                    '/users/:alpha' => 'User\Profile',
                    '/users/:alpha/edit' => 'User\Edit',
                    '/users/:alpha/audit' => 'User\Audit',
                    '/users/:alpha/activate' => 'Account\Activate',
                    '/administrator' => 'Manage\Administrator\Portal',
                    '/administrator/logs' => 'Manage\Administrator\Logs',
                    '/moderator' => 'Manage\Moderator\Portal',
                    '/moderator/approve/:number' => 'Manage\Moderator\Approve',
                    '/moderator/tickets' => 'Manage\Moderator\Tickets',
                    '/moderator/tickets/:number' => 'Manage\Moderator\Ticket',
                    '/banners' => 'Banners\Portal',
                    '/banners/:number' => 'Banners\Banner',
                    '/banners/:number/:alpha' => 'Banners\Translation',
                    '/register' => 'Account\Register',
                    '/login' => 'Account\Login',
                    '/logout' => 'Account\Logout',
                    '/settings' => 'Account\Settings',
                    '/identify' => 'Account\Identify',
                    '/auth' => 'Error\BadRequest',
                    '/auth/gateway' => 'Error\BadRequest',
                    '/auth/callback' => 'Error\BadRequest',
                    '/auth/gateway/:alpha' => 'Auth\Gateway',
                    '/auth/callback/:alpha' => 'Auth\Callback',
                    '/about' => 'Site\About',
                    '/about/api' => 'Site\Api',
                    '/about/clonk' => 'Site\Clonk',
                    '/privacy' => 'Site\Privacy',
                    '/contact' => 'Site\Contact',
                    '/language' => 'Site\Language',
                ));
                $router->addRoutes(array(
                    '/api/internal/addons/:number/:version/query' => 'Api\Internal\Release\QueryFile',
                    '/api/internal/addons/:number/:version/remove' => 'Api\Internal\Release\RemoveFile',
                    '/api/internal/addons/:number/:version/upload' => 'Api\Internal\Release\UploadFile',
                    '/api/internal/addons/:number/:version/dependencies' => 'Api\Internal\Release\QueryDependencies',
                    '/api/internal/upload' => 'Api\Internal\UploadFile',
                    '/api/internal/banners/remove/:number' => 'Api\Internal\Banners\RemoveBanner',
                ));
                // api routes
                $router->addRoutes(array(
                    '/api/v([0-9]+)/games\\.json' => 'Api\Games',
                    '/api/v([0-9]+)/addons/:alpha\\.json' => 'Api\Game',
                    '/api/v([0-9]+)/addons/:alpha/:alpha\\.json' => 'Api\Release',
                    '/api/v([0-9]+)/addons/:alpha/:alpha/:version\\.json' => 'Api\Release',
                ));
            } else {
                $router->addRoutes(array(
                    '/' => 'Site\Disabled'
                ));
            }

            // determine the RESTful method
            $method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD',
                    FILTER_SANITIZE_STRING));

            // determine the controller
            $presenterClass = $router->route($request);

            if (!$this->container->has($presenterClass)) {
                throw new FileNotFoundException('presenter matched but was not found');
            }

            // check if method is supported
            if (!method_exists($presenterClass, $method) || ($method !== 'get' && $method
                !== 'post')) {
                throw new NotImplementedException($presenterClass.'->'.$method.'()');
            }

            // execute the RESTful method
            $presenter = $this->container->get($presenterClass);
            $presenter->setRequest($request);
            $presenter->handle($method, $router->getMatches());
            $this->logger->debug('successfully handled request');
        } catch (Exception $exception) {
            $presenterClass = $exception->getPresenter();

            if ($this->container->has($presenterClass)) {
                $this->container->get($presenterClass)->get($exception);
            } elseif (!empty($presenterClass)) {
                throw new PHPException('failed to get error presenter "'.$presenterClass.'"');
            }
        } catch (PHPException $exception) {
            try {
                $this->container->get(\PDO::class); // attempt to instantiate PDO
            }
            catch(\RuntimeException $exception) {
                $this->container->get(\Lorry\TemplateEngineInterface::class)->addGlobal('site_enabled', false);
                $this->container->get(\Lorry\Presenter\Error\DatabaseDown::class)->get($exception);
                return;
            }
            $this->container->get(\Lorry\Presenter\Error\InternalError::class)->get($exception);
            return;
        }
    }

    public function getProjectRoot() {
        return self::PROJECT_ROOT;
    }

    /**
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
