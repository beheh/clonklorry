<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class FileNotFound extends Presenter\Error {

	protected function getCode() {
		return 404;
	}

	protected function getMessage() {
		return 'File Not Found';
	}

	protected function getLocalizedMessage() {
		return gettext('File not found');
	}

	protected function getLocalizedDescription() {
		return gettext('The file you requested could not be found.');
	}

}
