<?php

namespace Lorry\Presenter;

use \Lorry\Presenter;

abstract class Redirect extends Presenter {

	public abstract function getLocation();

	public function get() {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$this->getLocation());

	}
}