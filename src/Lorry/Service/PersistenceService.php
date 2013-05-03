<?php

namespace Lorry\Service;

use \PDO;
use \Exception;

class PersistenceService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function setConfig(ConfigService $config) {
		self::$config = $config;
	}

}