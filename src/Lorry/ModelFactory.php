<?php

namespace Lorry;

use \Lorry\Service\PersistenceService;
use \Exception;

class ModelFactory {

	/**
	 *
	 * @var \Lorry\Persistence
	 */
	private static $persistence;

	public static function setPersistence(PersistenceService $persistence) {
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
			throw new Exception('unknown model');
		}
		$instance = new $class();
		if(!$instance instanceof ModelInterface) {
			throw new Exception('model does not implement interface');
		}
		$instance->setPersistence(self::$config);
		return $instance;
	}

}