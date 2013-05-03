<?php

namespace Lorry;

use Lorry\Exception\FileNotFoundException;

abstract class Router {

	private static $routes;

	public function __construct() {
		self::$routes = array();
	}

	public static function addRoutes($route) {
		self::$routes = array_merge($array1, self::$routes);
	}

	public static function setRoutes($routes) {
		self::$routes = $routes;
	}

	private static $matches;

	public static function getMatches() {
		return self::$matches;
	}

	/**
	 * Returns the presenter matching to the request.
	 * @return \Lorry\Presenter
	 */
	public static function route() {
		$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

		$tokens = array(
			':string' => '([a-zA-Z]+)',
			':number' => '([0-9]+)',
			':alpha' => '([a-zA-Z0-9-_]+)',
			':version' => '(([0-9]+\.)*[0-9]+(-[a-zA-Z0-9-_]+)?)'
		);

		foreach(self::$routes as $pattern => $presenter) {
			$pattern = strtr($pattern, $tokens);
			if(preg_match('#^/?'.$pattern.'/?$#', $path, $matches)) {
				unset($matches[0]);
				self::$matches = $matches;
				return PresenterFactory::build($presenter);
			}
		}

		throw new FileNotFoundException($path);
	}

}