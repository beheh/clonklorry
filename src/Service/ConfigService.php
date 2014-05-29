<?php

namespace Lorry\Service;

use Exception;
use Symfony\Component\Yaml\Yaml;

class ConfigService {

	const DIR = '../app/config/';

	private $config;

	public function __construct() {
		$config = array();
		$file = self::DIR.'lorry.yml';
		if(!file_exists($file)) {
			throw new Exception('config file not found (at '.$file.')');
		}
		$this->config = Yaml::parse(file_get_contents($file));
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

	public function getSize($key) {
		$size = $this->get($key);
		$suffixes = array(
			'' => 1,
			'k' => 1024,
			'm' => 1048576, // 1024 * 1024
			'g' => 1073741824, // 1024 * 1024 * 1024
		);
		$match = null;
		if(preg_match('/([0-9]+)\s*(k|m|g)?(b?(ytes?)?)/i', $size, $match)) {
			return $match[1] * $suffixes[strtolower($match[2])];
		}
		return 0;
	}

	public function getTracking() {
		$file = self::DIR.'tracking.html';
		if(file_exists($file)) {
			return file_get_contents($file);
		}
		return false;
	}

}
