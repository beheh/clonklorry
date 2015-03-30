<?php

namespace Lorry;

use Lorry\Exception\NotImplementedException;
use Lorry\Router;
use Lorry\Service\ConfigService;
use Lorry\Logger\MonologLoggerFactory;

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
        $this->logger = $loggerFactory->build('environment');
        $this->logger->info('starting up');

        $config = new ConfigService($loggerFactory);

        $builder = new \DI\ContainerBuilder();
        $builder->useAnnotations(true);

        if (!$config->get('debug') && function_exists('apc_store')) {
            $cache = new \Doctrine\Common\Cache\ApcCache();
            $cache->setNamespace($config->get('brand'));
            $builder->setDefinitionCache($cache);
        }

        $container = $builder->build();
        $container->set('Lorry\Service\ConfigService', $config);
        $this->container = $container;

        error_reporting(E_ALL ^ E_STRICT);

        $container->set('Interop\Container\ContainerInterface', $container);
        $container->set('Lorry\Logger\LoggerFactoryInterface', $loggerFactory);

        $container->set('Psr\Log\LoggerInterface', \DI\factory(function() use ($loggerFactory) {
                    return $loggerFactory->build('default');
                }));
        $container->set('loggerFactory', \DI\link('Lorry\Logger\LoggerFactoryInterface'));
        $container->set('logger', \DI\link('Psr\Log\LoggerInterface'));

        $container->set('config', \DI\link('Lorry\Service\ConfigService'));
        $container->set('persistence', \DI\link('Lorry\Service\PersistenceService'));
        $container->set('localisation', \DI\link('Lorry\Service\LocalisationService'));
        $container->set('mail', \DI\link('Lorry\Service\MailService'));
        $container->set('job', \DI\link('Lorry\Service\JobService'));
        $container->set('session', \DI\link('Lorry\Service\SessionService'));
        $container->set('security', \DI\link('Lorry\Service\SecurityService'));
        $container->set('cdn', \DI\link('Lorry\Service\CdnService'));
        $container->set('router', new Router($loggerFactory->build('router'), $container));
        $container->set('twig', \DI\link('Lorry\TemplateEngineInterface'));

        $container->set('Predis\Client', \DI\factory(function() use ($config) {
                    return new \Predis\Client($config->get('job/dsn'));
                }));

        $container->set('BehEh\Flaps\Flaps', \DI\factory(function() use ($container) {
                    $adapter = new \BehEh\Flaps\Storage\PredisStorage($container->get('Predis\Client'), array('prefix' => 'clonklorry:'));
                    $flaps = new \BehEh\Flaps\Flaps($adapter);
                    $flaps->setDefaultViolationHandler(new Adapter\LorryViolationHandler);
                    return $flaps;
                }));

        $container->set('Lorry\TemplateEngineInterface', \DI\factory(function() use ($config) {
                    $loader = new \Twig_Loader_Filesystem(__DIR__.'/../app/templates');
                    $twig = new Adapter\TwigTemplatingEngineAdapter($loader, array('cache' => __DIR__.'/../cache/twig', 'debug' => $config->get('debug')));
                    $twig->addExtension(new \Twig_Extension_Escaper(true));
                    $twig->addExtension(new \Twig_Extensions_Extension_I18n());
                    return $twig;
                }));

        \Monolog\ErrorHandler::register($loggerFactory->build('errorHandler'));

        $templating = $container->get('Lorry\TemplateEngineInterface');
        $templating->addGlobal('brand', htmlspecialchars($config->get('brand')));
        $templating->addGlobal('base', htmlspecialchars($config->get('base')));
        $templating->addGlobal('resources', htmlspecialchars($config->get('base').'/resources'));
        $templating->addGlobal('site_copyright', htmlspecialchars('© '.date('Y')));
        $templating->addGlobal('site_trademark', '<a class="text" href="http://clonk.de">'.gettext('"Clonk" is a registered trademark of Matthes Bender').'</a>');
        $templating->addGlobal('site_enabled', $config->get('enable/site'));
        $templating->addGlobal('site_notice', $config->get('notice/text'));
        $templating->addGlobal('site_notice_class', $config->get('notice/class'));
        $templating->addGlobal('site_tracking', $config->getTracking());
        $templating->addGlobal('enable', array('upload' => $config->get('enable/upload')));

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

            $twig = $this->container->get('Lorry\TemplateEngineInterface');
            $twig->addGlobal('path', explode('/', trim($request->getPathInfo(), '/')));
            $twig->addGlobal('origpath', trim($request->getPathInfo()));
            $twig->addGlobal('filename', htmlspecialchars(rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')));
            $language = $localisation->getDisplayLanguage();
            $twig->addGlobal('locale', str_replace('-', '_', $language));
            $languages = $localisation->getAvailableLanguages();
            $twig->addGlobal('nextlocale', strstr($languages[(array_search($language, $languages) + 1) % count($languages)], '-', true));
            $twig->addGlobal('fbid', $config->get('oauth/facebook/id'));

            $session = $this->container->get('session');
            $twig->addGlobal('knows_clonk', $session->getFlag('knows_clonk'));

            if ($session->authenticated()) {
                $user = $session->getUser();
                $twig->addGlobal('user_login', true);
                $twig->addGlobal('user_name', $user->getUsername());
                $twig->addGlobal('user_profile', $config->get('base').'/users/'.$user->getUsername());
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
                    '/users' => 'User\Table',
                    '/users/:alpha' => 'User\Profile',
                    '/users/:alpha/edit' => 'User\Edit',
                    '/users/:alpha/activate' => 'Account\Activate',
                    '/administrator' => 'Manage\Administrator\Portal',
                    '/administrator/logs' => 'Manage\Administrator\Logs',
                    '/moderator' => 'Manage\Moderator\Portal',
                    '/moderator/approve/:number' => 'Manage\Moderator\Approve',
                    '/moderator/tickets' => 'Manage\Moderator\Tickets',
                    '/moderator/tickets/:number' => 'Manage\Moderator\Ticket',
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
            $method = strtolower(filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING));

            // determine the controller
            $presenterClass = $router->route($request);

            if (!$this->container->has($presenterClass)) {
                throw new FileNotFoundException('presenter matched but was not found');
            }

            // check if method is supported
            if (!method_exists($presenterClass, $method) || ($method !== 'get' && $method !== 'post')) {
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
            }
            elseif(!empty($presenterClass)) {
                throw new \Exception('failed to get error presenter "'.$presenterClass.'"');
            }
        } catch (\Exception $exception) {
            if ($this->container->get('persistence')->hasFailed()) {
                $this->container->get('Lorry\TemplateEngineInterface')->addGlobal('site_enabled', false);
                $this->logger->alert('cannot reach database');
            }
            $this->container->get('Lorry\Presenter\Error')->get($exception);
        }
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
