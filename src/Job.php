<?php

namespace Lorry;

use Resque\AbstractJob;

/**
 * @property \Lorry\Service\ConfigService $config
 * @property \Lorry\Service\PersistenceService $persistence
 * @property \Lorry\Service\LocalisationService $localisation
 * @property \Lorry\Service\MailService $mail
 * @property \Lorry\Service\JobService $job
 * @property \Lorry\Service\SessionService $session
 * @property \Lorry\Service\SecurityService $security
 * @property \Lorry\Service\FileService $file
 * @property \Lorry\Router $router
 * @property \Lorry\TemplateEngineInterface $twig
 * 
 */
abstract class Job extends AbstractJob
{

    public abstract function execute();
    protected $container;

    final public function perform()
    {
        try {
            $environment = new Environment();
            $environment->setup();
            $this->container = $environment->getContainer();
            $this->execute();
        } catch (\Exception $ex) {
            // throw on if we can't access the LoggerBuilder, so Exception appears in the worker log
            if(!$this->container) {
                throw $ex;
            }
            $loggerFactory = $this->container->get('Lorry\Logger\LoggerFactoryInterface');
            $logger = $loggerFactory->build('job');
            $logger->error($ex);
        }
    }

    public function __get($name)
    {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }
    }
}
