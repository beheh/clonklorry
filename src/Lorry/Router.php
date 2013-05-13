<?php

namespace Lorry;

use Lorry\Exception\FileNotFoundException;

abstract class Router {

	private static $routes;

	public function __construct() {
		self::$routes = array();
	}

	public static function addRoutes($route) {
		self::$routes = array_merge($route, self::$routes);
	}

	public static function setRoutes($routes) {
		self::$routes = $routes;
	}

	private static $matches;

	public static function getMatches() {
		return self::$matches;
	}

	public static function getPath() {
		return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
	}

	/**
	 * Returns the presenter matching to the request.
	 * @return \Lorry\Presenter
	 */
	public static function route() {
		$path = self::getPath();

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