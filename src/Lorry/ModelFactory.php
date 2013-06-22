<?php

namespace Lorry;

use Lorry\Service\ConfigService;
use Lorry\Service\PersistenceService;
use Exception;

class ModelFactory {

	/**
	 *
	 * @var \Lorry\Config
	 */
	private static $config;

	public static function setConfigService(ConfigService $config) {
		self::$config = $config;
	}

	/**
	 *
	 * @var \Lorry\Persistence
	 */
	private static $persistence;

	public static function setPersistenceService(PersistenceService $persistence) {
		self::$persistence = $persistence;
	}

	/**
	 *
	 * @param string $model
	 * @return \Lorry\Model
	 * @throws Exception
	 */
	public static function build($model) {
		$class = '\\Lorry\\Model\\'.$model;
		if(!class_exists($class)) {
			throw new Exception('unknown model "'.$class.'"');
		}
		$instance = new $class();
		if(!$instance instanceof ModelInterface) {
			throw new Exception('model does not implement interface');
		}
		$instance->setConfigService(self::$config);
		$instance->setPersistenceService(self::$persistence);
		return $instance;
	}

}