<?php

namespace Lorry\Job;

use Resque\AbstractJob as ResqueAbstractJob;

/**
 * @property \Lorry\Service\ConfigService $config
 * @property \Lorry\Service\LocalisationService $localisation
 * @property \Lorry\Service\MailService $mail
 * @property \Lorry\Service\JobService $job
 * @property \Lorry\Service\SessionService $session
 * @property \Lorry\Service\SecurityService $security
 * @property \Lorry\Service\FileService $file
 * @property \Lorry\Router $router
 * @property \Lorry\TemplateEngineInterface $twig
 * @property \Doctrine\Common\Persistence\ObjectManager $manager
 */
abstract class AbstractJob extends ResqueAbstractJob
{

    abstract public function execute();
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
            if (!$this->container) {
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
