<?php

namespace Lorry\Service;

use Exception;

class ConfigService {

	const FILE = '../app/config/lorry.php';

	private $config;

	public function __construct() {
		$config = array();
		if(!file_exists(self::FILE)) {
			throw new Exception('config file not found (at '.self::FILE.')');
		}
		require self::FILE;
		$this->config = $config;
	}

	public function get($key) {
		if(isset($this->config[$key])) {
			return $this->config[$key];
		}
		return null;
	}
}