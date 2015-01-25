<?php

namespace Lorry\Exception;

use Lorry\Exception;

class AuthentificationFailedException extends Exception {

	public function getPresenter() {
		return 'Error\AuthFailed';
	}

}
