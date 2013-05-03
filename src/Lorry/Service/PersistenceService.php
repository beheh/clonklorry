<?php

namespace Lorry\Service;

use \PDO;

class PersistenceService {

	/**
	 *
	 * @var \Lorry\Config
	 */
	private static $config;

	public static function setConfig(ConfigService $config) {
		self::$config = $config;
	}

	/**
	 *
	 * @var PDO
	 */
	private $connection = null;

	public function ensureConnected() {
		if($this->connection)
			return true;
		$this->connection = new PDO(
		$this->config->get('database/dsn'),
		$this->config->get('database/username'),
		$this->config->get('database/password'));
	}

}