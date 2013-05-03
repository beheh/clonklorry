<?php

namespace Lorry\Service;

class SessionService {

	/**
	 *
	 * @var \Lorry\Service\Config
	 */
	protected $config;

	public function setConfig(ConfigService $config) {
		$this->config = $config;
	}

}