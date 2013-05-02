<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class Forbidden extends Presenter\Error {

	protected function getCode() {
		return 403;
	}

	protected function getMessage() {
		return 'Forbidden';
	}

}