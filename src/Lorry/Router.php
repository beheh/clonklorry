<?php

namespace Lorry;

class Router extends Object {

	/**
	 * Returns the view matching to the request, sanitazing the query.
	 * @return Lorry_View
	 */
	public function route() {
		$path = 'index';
		if(isset($_GET['__path']) && !empty($_GET['__path'])) {
			$path = $_GET['__path'];
			$path = preg_replace(array('|\.|', '|\0|'), '', $path);
			$path = preg_replace(array('|_+|', '|/+|'), array('_', '/'), $path);
		}
		return $this->custom($path);
	}

	/**
	 * Returns the specified view after checking for access and existence.
	 * @param string $which
	 * @return Lorry_View
	 */
	public function custom($which) {

		//split the request
		$original = explode('/', $which);
		$components = array();
		foreach($original as $key => $component) {
			$components[$key] = ucfirst($component);
		}

		$view = false;

		//exactly the requested path
		if($view = $this->castView(implode('\\', $components))) {
			return $this->access($view);
		}

		//check if parent does wildcard
		array_pop($components);

		if($view = $this->castView(implode('\\', $components))) {
			if($view->access()) {
				if($view->wildcard($original[count($original) - 1])) {
					//parent wildcards
					return $view;
				}
			} else {
				return new Views\Error\Forbidden($this->lorry);
			}
			$view = false;
		}

		//otherwise iterate upwards until forbidden or none left
		while(count($components) && !$view) {
			if($view = $this->castView(implode('\\', $components))) {
				if(!$view->access()) {
					return new Views\Error\Forbidden($this->lorry);
				}
				break;
			}

			array_pop($components);
		}

		//reached top without match, nothing found
		return new Views\Error\Notfound($this->lorry);
	}

	/**
	 *
	 * @param Lorry_View $view
	 * @return Lorry_View
	 */
	protected function access(View $view) {
		if($view->access()) {
			return $view;
		}

		return new Views\Error\Forbidden($this->lorry);
	}

	/**
	 *
	 * @return array
	 */
	final public function getRequestedPath() {
		$path = isset($_GET['__path']) ? $_GET['__path'] : '';
		return explode('/', strtolower($path));
	}

	/**
	 *
	 * @param type $class
	 * @return Lorry_View|boolean
	 */
	final private function castView($class) {
		$class = '\\Lorry\\Views\\'.$class;
		if(class_exists($class)) {
			return new $class($this->lorry);
		}
		return false;
	}

}