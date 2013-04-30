<?php

namespace Lorry\Views\Error;

use Lorry\Environment;
use Lorry\Views\Error;

class Notfound extends Error {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		header('HTTP/1.1 404 Not Found');
		$this->setMessage(gettext('file not found'));
	}

}