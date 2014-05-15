<?php

namespace Lorry;

use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\OutputCompleteException;

abstract class Router {

	private static $routes = array();

	public function __construct() {

	}

	public static function addRoutes($route) {
		self::$routes = array_merge($route, self::$routes);
	}

	private static $matches;

	public static function getMatches() {
		return self::$matches;
	}

	public static function getPath() {
		$path = '';
		$path_info = filter_input(INPUT_SERVER, 'PATH_INFO');
		if($path_info) {
			// fallback if no mod_rewrite
			$path = $path_info;
		} else {
			$request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
			// don't pass get parameters
			if(strpos($request_uri, '?') !== false) {
				$request_uri = substr($request_uri, 0, strpos($request_uri, '?'));
			}
			// filter out prefix directory if exists
			$base = filter_input(INPUT_SERVER, 'BASE');
			if($base) {
				$path = substr($request_uri, strlen($base));
			} else {
				$path = $request_uri;
			}
		}
		$path = trim($path);
		if(!$path) {
			$path = '/';
		}
		return $path;
	}

	/**
	 * Returns the presenter matching to the request.
	 * @return \Lorry\Presenter
	 */
	public static function route() {
		$path = self::getPath();

		if($path != '/' && substr($path, strlen($path) - 1, 1) == '/') {
			$request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
			header('Location: '.substr($request_uri, 0, strlen($request_uri) - 1));
			throw new OutputCompleteException;
		}

		$tokens = array(
			':string' => '([a-zA-Z]+)',
			':number' => '([0-9]+)',
			':alpha' => '([a-zA-Z0-9-_]+)',
			':version' => '([a-zA-Z0-9-.]+)'
		);

		foreach(self::$routes as $pattern => $presenter) {
			$pattern = strtr($pattern, $tokens);
			if(preg_match('#^/?'.$pattern.'/?$#', $path, $matches)) {
				unset($matches[0]);
				self::$matches = $matches;
				return PresenterFactory::build($presenter);
			}
		}

		throw new FileNotFoundException('no matching presenter for path "'.$path.'"');
	}

}
