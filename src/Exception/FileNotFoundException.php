<?php

namespace Lorry\Exception;

use Lorry\Exception;

class FileNotFoundException extends Exception {

	public function getPresenter() {
		return 'Error\FileNotFound';
	}

	public function getApiType() {
		return 'notfound';
	}

	public function getHttpCode() {
		return 404;
	}

	public function getHttpMessage() {
		return 'File Not Found';
	}

}
