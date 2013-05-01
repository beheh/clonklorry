<?php

namespace Lorry;

class Router extends Object {

	/**
	 * Returns the presenter matching to the request, sanitazing the query.
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
	 * Returns the specified presenter after checking for access and existence.
	 * @param string $which
	 * @return Lorry_Presenter
	 */
	public function custom($which) {

		//split the request
		$original = explode('/', $which);
		$components = array();
		foreach($original as $key => $component) {
			$components[$key] = ucfirst($component);
		}

		$presenter = false;

		//exactly the requested path
		if($presenter = $this->castPresenter(implode('\\', $components))) {
			return $this->access($presenter);
		}

		//check if parent does wildcard
		array_pop($components);

		if($presenter = $this->castPresenter(implode('\\', $components))) {
			if($presenter->access()) {
				if($presenter->wildcard($original[count($original) - 1])) {
					//parent wildcards
					return $presenter;
				}
			} else {
				return new Presenters\Error\Forbidden($this->lorry);
			}
			$presenter = false;
		}

		//otherwise iterate upwards until forbidden or none left
		while(count($components) && !$presenter) {
			if($presenter = $this->castPresenter(implode('\\', $components))) {
				if(!$presenter->access()) {
					return new Presenters\Error\Forbidden($this->lorry);
				}
				break;
			}

			array_pop($components);
		}

		//reached top without match, nothing found
		return new Presenters\Error\Notfound($this->lorry);
	}

	/**
	 *
	 * @param Lorry_Presenter $presenter
	 * @return Lorry_Presenter
	 */
	protected function access(Presenter $presenter) {
		if($presenter->access()) {
			return $presenter;
		}

		return new Presenters\Error\Forbidden($this->lorry);
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
	 * @return Lorry_Presenter|boolean
	 */
	final private function castPresenter($class) {
		$class = '\\Lorry\\Presenters\\'.$class;
		if(class_exists($class)) {
			return new $class($this->lorry);
		}
		return false;
	}

}