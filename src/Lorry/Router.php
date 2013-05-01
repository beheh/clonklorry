<?php

namespace Lorry;

class Router {

	private static $lorry;
	
	public function __construct(Environment $lorry) {
		self::$lorry = $lorry;
	}
	
	private static $routes;

	public static function setRoutes($routes) {
		self::$routes = $routes;
	}

	/**
	 * Returns the presenter matching to the request, sanitazing the query.
	 * @return Lorry_View
	 */
	public static function route() {
		$path = 'index';
		if(isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '/') {
			$path = ltrim($_SERVER['PATH_INFO'], '/');
			$path = preg_replace(array('|\.|', '|\0|'), '', $path);
			$path = preg_replace(array('|_+|', '|/+|'), array('_', '/'), $path);
		}
		return self::custom($path);
	}

	/**
	 * Returns the specified presenter after checking for access and existence.
	 * @param string $which
	 * @return Lorry_Presenter
	 */
	public static function custom($which) {

		//split the request
		$original = explode('/', $which);
		$components = array();
		foreach($original as $key => $component) {
			$components[$key] = ucfirst($component);
		}

		$presenter = false;

		//exactly the requested path
		if($presenter = self::castPresenter(implode('\\', $components))) {
			return self::access($presenter);
		}

		//check if parent does wildcard
		array_pop($components);

		if($presenter = self::castPresenter(implode('\\', $components))) {
			if($presenter->access()) {
				if($presenter->wildcard($original[count($original) - 1])) {
					//parent wildcards
					return $presenter;
				}
			} else {
				return new Presenters\Error\Forbidden(self::$lorry);
			}
			$presenter = false;
		}

		//otherwise iterate upwards until forbidden or none left
		while(count($components) && !$presenter) {
			if($presenter = self::castPresenter(implode('\\', $components))) {
				if(!$presenter->access()) {
					return new Presenters\Error\Forbidden(self::$lorry);
				}
				break;
			}

			array_pop($components);
		}

		//reached top without match, nothing found
		return new Presenters\Error\Notfound(self::$lorry);
	}

	/**
	 *
	 * @param Lorry_Presenter $presenter
	 * @return Lorry_Presenter
	 */
	protected static function access(Presenter $presenter) {
		if($presenter->access()) {
			return $presenter;
		}

		return new Presenters\Error\Forbidden(self::$lorry);
	}

	/**
	 *
	 * @return array
	 */
	public static function getRequestedPath() {
		$path = isset($_GET['__path']) ? $_GET['__path'] : '';
		return explode('/', strtolower($path));
	}

	/**
	 *
	 * @param type $class
	 * @return Lorry_Presenter|boolean
	 */
	private static function castPresenter($class) {
		$class = '\\Lorry\\Presenters\\'.$class;
		if(class_exists($class)) {
			return new $class(self::$lorry);
		}
		return false;
	}

}