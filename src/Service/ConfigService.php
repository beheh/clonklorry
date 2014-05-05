<?php

namespace Lorry\Service;

use Exception;
use Symfony\Component\Yaml\Yaml;

class ConfigService {

	const FILE = '../app/config/lorry.yml';

	private $config;

	public function __construct() {
		$config = array();
		if(!file_exists(self::FILE)) {
			throw new Exception('config file not found (at '.self::FILE.')');
		}
		$this->config = Yaml::parse(file_get_contents(self::FILE));
	}

	public function get($key) {
		$keys = explode('/', $key);
		$subset = $this->config;
		foreach($keys as $current) {
			if(!isset($subset[$current])) {
				return null;
			}
			$subset = $subset[$current];
		}
		return $subset;
	}
}