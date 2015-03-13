<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class TooManyRequests extends Presenter\Error {

	protected function getCode() {
		return 429;
	}

	protected function getMessage() {
		return 'Too Many Requests';
	}

	protected function getLocalizedMessage() {
		return gettext('Too many requests');
	}

	protected function getLocalizedDescription() {
		return gettext('You have exceeded the allowed number of requests.');
	}

}
