<?php

namespace Lorry;

use Lorry\Logger\LoggerFactoryInterface;

abstract class Service {
	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	public function __construct(LoggerFactoryInterface $loggerFactory) {
		$this->logger = $loggerFactory->build($this->getLogChannel());
		$this->logger->debug('initialized '.get_class($this));
	}

	public function getLogChannel() {
		return 'service';
	}
}