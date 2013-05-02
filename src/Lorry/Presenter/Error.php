<?php

namespace Lorry\Presenter;

use \Lorry\Presenter;
use \Exception;

class Error extends Presenter {

	protected function getCode() {
		return 500;
	}

	protected function getMessage() {
		return 'Internal Server Error';
	}

	public function get(Exception $exception) {
		header('HTTP/1.1 '.$this->getCode().' '.$this->getMessage());
		echo $this->getMessage();
	}
}