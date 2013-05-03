<?php

namespace Lorry\Service;

class SessionService {

	/**
	 *
	 * @var \Lorry\Config
	 */
	private static $config;

	public static function setConfig(ConfigService $config) {
		self::$config = $config;
	}

}