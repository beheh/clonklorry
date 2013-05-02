<?php

namespace Lorry;

class Config {

	private $config;

	public function __construct() {
		require '../app/config/lorry.php';
		$this->config = $config;
	}

	public function get($key) {
		if(isset($this->config[$key])) {
			return $this->config[$key];
		}
		return null;
	}
}

?>
