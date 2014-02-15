<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class AuthFailed extends Presenter\Error {

	protected function getCode() {
		return 500;
	}

	protected function getMessage() {
		return 'Authentification failed';
	}

	protected function getLocalizedMessage() {
		return gettext('Authentification failed');
	}

	protected function getLocalizedDescription() {
		return gettext('The authentification failed.');
	}

}