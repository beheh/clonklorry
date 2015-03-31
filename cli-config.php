<?php
/**
 * CLI Config file
 *
 * This file is sourced by the Resque command line tools. It should be copied out to the project that wants to use
 * Resque, and modified to suit. The only important thing is that this file returns a HelperSet, with a 'redis' helper,
 * so that Resque can talk to your Redis instance.
 */

(include_once(__DIR__ . '/app/bootstrap.php'));

$environment = new Lorry\Environment();
$environment->setup();
$container = $environment->getContainer();

// we don't use Lorry's LoggerFactory so we don't spam the default channel with Resque entries
$logger = new Monolog\Logger('resque');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::NOTICE));

// the jobs use LoggerFactorys logger anyway should any exception occur

return \Resque\Console\ConsoleRunner::createHelperSet($container->get('Predis\Client'), $logger);
