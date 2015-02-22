<?php

namespace Lorry;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\OutputCompleteException;
use Symfony\Component\HttpFoundation\Request;

class Router {

	/**
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 *
	 * @var \Interop\Container\ContainerInterface
	 */
	protected $container;

	public function __construct(LoggerInterface $logger, ContainerInterface $container) {
		$this->logger = $logger;
		$this->container = $container;
	}

	private $routes = array();

	public function addRoutes($route) {
		$this->routes = array_merge($route, $this->routes);
	}

	private $matches;

	public function getMatches() {
		return $this->matches;
	}

	protected $prefix;

	public function setPrefix($prefix) {
		$prefix = rtrim($prefix, '\\');
		$prefix .= '\\';
		$this->prefix = $prefix;
	}

	/**
	 * Returns the presenter matching to the request.
	 * @return string
	 */
	public function route(Request $request) {
		$path = $request->getPathInfo();

		if($path != '/' && substr($path, strlen($path) - 1, 1) == '/') {
			$request_uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
			header('Location: '.substr($request_uri, 0, strlen($request_uri) - 1), true, 301);
			throw new OutputCompleteException;
		}

		$tokens = array(
			':string' => '([a-zA-Z]+)',
			':number' => '([0-9]+)',
			':alpha' => '([a-zA-Z0-9-_]+)',
			':version' => '([a-zA-Z0-9-.]+)'
		);

		foreach($this->routes as $pattern => $presenter) {
			$pattern = strtr($pattern, $tokens);
			if(preg_match('#^/?'.$pattern.'/?$#', $path, $matches)) {
				unset($matches[0]);
				$this->matches = $matches;
				$this->logger->debug('matched route to '.$presenter);
				return $this->prefix.$presenter;
			}
		}

		throw new FileNotFoundException('no match for path "'.$path.'"');
	}

}
