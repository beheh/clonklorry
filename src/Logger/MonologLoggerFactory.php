<?php

namespace Lorry\Logger;

use Interop\Container\ContainerInterface;
use Psr\Log\LogLevel;
use Lorry\Environment;

class MonologLoggerFactory implements LoggerFactoryInterface {

	protected $handlers = array();

	public function __construct() {
		$streamHandler = new \Monolog\Handler\StreamHandler(Environment::PROJECT_ROOT.'/logs/lorry.log');
		$streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter());
		$fingersCrossedHandler = new \Monolog\Handler\FingersCrossedHandler($streamHandler, new \Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy(LogLevel::ERROR));

		$this->handlers[] = $fingersCrossedHandler;
	}

	public function build($channel) {
		$logger = new \Monolog\Logger($channel);

		foreach($this->handlers as $handler) {
			$logger->pushHandler($handler);
		}

		return $logger;
	}

}
