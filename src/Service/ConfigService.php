<?php

namespace Lorry\Service;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use Lorry\Environment;

class ConfigService {

	private $config;

	public function __construct() {
		$this->config = array();
		$file = Environment::PROJECT_ROOT.'/config/lorry.yml';
		if(!file_exists($file)) {
			throw new InvalidArgumentException('config file not found (at '.$file.')');
		}
		$this->config = Yaml::parse(file_get_contents($file));
	}

	/**
	 * 
	 * @param string $key
	 * @return mixed
	 * @throws Exception
	 */
	public function get($key) {
		$keys = explode('/', $key);
		$subset = $this->config;
		foreach($keys as $current) {
			if(!isset($subset[$current])) {
				return null;
			}
			$subset = $subset[$current];
		}
		if(is_array($subset) && !empty($subset)) {
			throw new Exception('"'.$key.'" is not a valid config key');
		}
		return $subset;
	}

	/**
	 * 
	 * @param string $key
	 * @return int
	 */
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

	/**
	 * 
	 * @return string
	 */
	public function getTracking() {
		$file = Environment::PROJECT_ROOT.'/config/tracking.html';
		if(file_exists($file)) {
			return file_get_contents($file);
		}
		return '';
	}

}
