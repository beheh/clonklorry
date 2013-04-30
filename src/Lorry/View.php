<?php

namespace Lorry;

use Lorry\Models\User;

abstract class View extends Object {

	abstract protected function render();

	abstract protected function allow();

	protected function allowUser(User $user) {
		return $this->allow();
	}

	protected function hasWildcard($wildcard) {
		return false;
	}

	protected function renderWildcard() {

	}

	/**
	 *
	 * @return string
	 */
	final public function display() {
		if($this->wildcard !== false) {
			return $this->renderWildcard();
		}
		return $this->render();
	}

	/**
	 * Whether or not the current request is allowed to interact withthe view.
	 * @return boolean
	 */
	final public function access() {
		if($this->lorry->session->authenticated()) {
			return $this->allowUser($this->lorry->session->getUser());
		}
		return $this->allow();
	}

	private $wildcard = false;

	/**
	 *
	 * @param string $wildcard
	 * @return boolean
	 */
	final public function wildcard($wildcard) {
		if($this->hasWildcard($wildcard)) {
			$this->wildcard = $wildcard;
			return true;
		}
		return false;
	}

	/**
	 * @TODO zu service?
	 * @param string $to
	 */
	final protected function redirect($to) {
		header('Location: '.$to);
		header('HTTP/1.1 302 Moved Permanently');
		exit();
	}

	final public function __toString() {
		return get_class($this);
	}

}

