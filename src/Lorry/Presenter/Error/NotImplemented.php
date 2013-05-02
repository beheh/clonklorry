<?php

namespace Lorry\Presenter\Error;

use \Lorry\Presenter;

class NotImplemented extends Presenter\Error {

	protected function getCode() {
		return 501;
	}

	protected function getMessage() {
		return 'Not implemented';
	}

}