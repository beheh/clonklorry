<?php

namespace Lorry\Service;

use Lorry\Service;

class Cache extends Service {

	private $memory;

	public function set($path, $value) {
		return false;
	}

	public function lookup($path) {
		$current = $this->memory;
		foreach($path as $segment) {
			if(!is_array($current))
				return false;
			if(!isset($current[$segment]))
				return false;
			$current = $current[$segment];
		}
		return $current;
	}

}