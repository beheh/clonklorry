<?php

class Lorry_Service_Config extends Lorry_Service {

	/**
	 * @var array contains the configuration values.
	 */
	private $config;

	/**
	 * Returns the global setting of the given key.
	 *
	 * @param $setting string key of the setting
	 * @return mixed value of the setting
	 * @throws UnexpectedValueException if the key isn't a valid configuration key.
	 */
	function __get($setting) {
		$this->checkLoaded();
		return $this->config[$setting];
	}

	/**
	 * Changes a global configuration value temporarily.
	 * @param $setting string name of the setting.
	 * @param $value mixed new value of the setting.
	 */
	function setConfig($setting, $value) {
		$this->config[$setting] = $value;
	}

	public function checkLoaded() {
		if(!isset($this->config)) {
			$this->loadConfigFile(ROOT.'config.php');
		}
	}

	/**
	 * Loads configuration values from a file.
	 *
	 * @param $filename string name of the configuration file.
	 * @throws RuntimeException if the file doesn't exist
	 */
	public function loadIniFile($filename) {
		if(is_file($filename)) {
			$values = parse_ini_file($filename);
			foreach($values as $setting => $value) {
				$this->setConfig($setting, $value);
			}
		} else {
			throw new RuntimeException('configuration file doesn\'t exist: '.$filename);
		}
	}

	/**
	 * Loads the configuration from the given file.
	 * @param $filename string path to the file.
	 */
	public function loadConfigFile($filename) {
		$config = array();
		$this->config = array();
		// import config file (which sets/modifies the $config array)
		require $filename;

		foreach($config as $key => $value) {
			$this->setConfig($key, $value);
		}
	}

}