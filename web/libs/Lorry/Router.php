<?php

class Lorry_Router extends Lorry_Object {

	/**
	 *
	 * @param Lorry_Environment $lorry
	 */
	public function __construct(Lorry_Environment $lorry) {
		$this->lorry = $lorry;
	}

	/**
	 * Returns the view matching to the request, sanitazing the query.
	 * @return Lorry_View
	 */
	public function route() {
		$path = 'index';
		if(isset($_GET['_path'])) {
			$path = preg_replace(array('|\.|', '|\0|'), '', $_GET['_path']);
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
		if($view = $this->cast('Lorry_View_'.implode('_', $components))) {
			return $this->access($view);
		}

		//check if parent does wildcard
		array_pop($components);

		if($view = $this->cast('Lorry_View_'.implode('_', $components))) {
			if($view->access()) {
				if($view->wildcard($original[count($original) - 1])) {
					//parent wildcards
					return $view;
				}
			} else {
				return new Lorry_View_Error_Forbidden($this->lorry);
			}
			$view = false;
		}

		//otherwise iterate upwards until forbidden or none left
		while(count($components) && !$view) {
			if($view = $this->cast('Lorry_View_'.implode('_', $components))) {
				if(!$view->access()) {
					return new Lorry_View_Error_Forbidden($this->lorry);
				}
				break;
			}

			array_pop($components);
		}

		//reached top without match, nothing found
		return new Lorry_View_Error_Notfound($this->lorry);
	}

	/**
	 *
	 * @param Lorry_View $view
	 * @return Lorry_View
	 */
	protected function access(Lorry_View $view) {
		if($view->access()) {
			return $view;
		}

		return new Lorry_View_Error_Forbidden($this->lorry);
	}

	/**
	 *
	 * @return array
	 */
	final public function getRequestedPath() {
		$path = isset($_GET['_path']) ? $_GET['_path'] : '';
		return explode('/', strtolower($path));
	}

	/**
	 *
	 * @param type $class
	 * @return Lorry_View|boolean
	 */
	final private function cast($class) {
		if(class_exists($class)) {
			return new $class($this->lorry);
		}
		return false;
	}

}

