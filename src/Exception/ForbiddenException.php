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
}