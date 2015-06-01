<?php
(include_once(__DIR__ . '/app/bootstrap.php'));

$environment = new Lorry\Environment();
$environment->setup();
$container = $environment->getContainer();

// we don't use Lorry's LoggerFactory so we don't spam the default channel with Resque entries
$logger = new Monolog\Logger('resque');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::NOTICE));
// the jobs use LoggerFactorys logger anyway should any exception occur

$resqueHelperSet = \Resque\Console\ConsoleRunner::createHelperSet($container->get('Predis\Client'), $logger);

// doctrine helper
$doctrineHelperSet = \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container->get('Doctrine\Common\Persistence\ObjectManager'));

$helpers = array_merge($resqueHelperSet->getIterator()->getArrayCopy(), $doctrineHelperSet->getIterator()->getArrayCopy());

return new \Symfony\Component\Console\Helper\HelperSet($helpers);
