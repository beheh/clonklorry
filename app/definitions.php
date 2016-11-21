<?php

return [
    \Psr\Log\LoggerInterface::class =>
    function ($container) {
        return $container->get(Lorry\Logger\LoggerFactoryInterface::class)->build('default');
    },
    \Lorry\Logger\LoggerFactoryInterface::class => \DI\get(\Lorry\Logger\MonologLoggerFactory::class),
    'loggerFactory' => \DI\get(\Lorry\Logger\LoggerFactoryInterface::class),
    'logger' => \DI\get(\Psr\Log\LoggerInterface::class),
    'config' => \DI\get(\Lorry\Service\ConfigService::class),
    \PDO::class => function($container) {
        $config = $container->get('config');
        try {
            $dsn = $config->get('persistence/dsn');
            $dbh = new \PDO($dsn, $config->get('persistence/username'), $config->get('persistence/password'));
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $dbh;
        } catch (\PDOException $ex) {
            // catch the pdo exception to prevent credential leaking (either logs or debug frontend)
            throw new RuntimeException('could not connect to database ('.$ex->getMessage().')');
        }
    },
    \Doctrine\Common\Persistence\ObjectManager::class => function($container) {
        $config = $container->get('config');

        $root = $container->get(\Lorry\Environment::class)->getProjectRoot();

        $doctrineConfig = new \Doctrine\ORM\Configuration();
        $cache = $container->get(\Doctrine\Common\Cache\Cache::class);
        $doctrineConfig->setMetadataCacheImpl($cache);
        $doctrineConfig->setQueryCacheImpl($cache);
        $doctrineConfig->setResultCacheImpl($cache);

        $doctrineConfig->setProxyDir($root.'/cache/doctrine');
        $doctrineConfig->setProxyNamespace('Lorry\ORM\Proxy');
        $doctrineConfig->setAutoGenerateProxyClasses(!!$config->get('debug'));

        $doctrineConfig->setMetadataDriverImpl($doctrineConfig->newDefaultAnnotationDriver($root.'/src/Model'));

        return \Doctrine\ORM\EntityManager::create(array('pdo' => $container->get('PDO')), $doctrineConfig);
    },
    'manager' => \DI\get(\Doctrine\Common\Persistence\ObjectManager::class),
    'localisation' => \DI\get(\Lorry\Service\LocalisationService::class),
    'mail' => \DI\get(\Lorry\Service\MailService::class),
    'job' => \DI\get(\Lorry\Service\JobService::class),
    'session' => \DI\get(\Lorry\Service\SessionService::class),
    'security' => \DI\get(\Lorry\Service\SecurityService::class),
    'file' => \DI\get(\Lorry\Service\FileService::class),
    'router' => function($container) {
        return new \Lorry\Router($container->get('loggerFactory')->build('router'), $container);
    },
    'twig' => \DI\get(\Lorry\TemplateEngineInterface::class),
    \Predis\Client::class =>
        function ($container) {
            $config = $container->get('config');
            return new \Predis\Client([
                'scheme' => 'unix',
	        'path' => $config->get('job/dsn'),
	    ]);
        },
    \BehEh\Flaps\Flaps::class =>
        function ($container) {
            $adapter = new \BehEh\Flaps\Storage\PredisStorage($container->get('Predis\Client'), array('prefix' => 'clonklorry:'));
            $flaps = new \BehEh\Flaps\Flaps($adapter);
            $flaps->setDefaultViolationHandler(new \Lorry\RateLimitViolationHandler);
            return $flaps;
        },
    \Lorry\TemplateEngineInterface::class =>
         function ($container) {
            $config = $container->get('config');
            $loader = new \Twig_Loader_Filesystem(__DIR__.'/../app/templates');
            $twig = new \Lorry\Adapter\TwigTemplatingEngineAdapter($loader, array('cache' => __DIR__.'/../cache/twig', 'debug' => $config->get('debug')));
            $twig->addExtension(new \Twig_Extension_Escaper(true));
            $twig->addExtension(new \Twig_Extensions_Extension_I18n());
            return $twig;
         }

];
