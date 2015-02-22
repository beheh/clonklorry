<?php

namespace Lorry\Exception;

use Lorry\Exception;

class BadRequestException extends Exception {

	public function getPresenter() {
		return 'Lorry\Presenter\Error\BadRequest';
	}

}
