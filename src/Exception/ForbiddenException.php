<?php

namespace Lorry\Exception;

use Lorry\Exception;

class ForbiddenException extends Exception {
	public function getPresenter() {
		return 'Error\Forbidden';
	}

	public function getApiType() {
		return 'forbidden';
	}

	public function getHttpCode() {
		return 403;
	}

	public function getHttpMessage() {
		return 'Forbidden';
	}
}