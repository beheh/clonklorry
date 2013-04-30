<?php

namespace Lorry\Views\Error;

use Lorry\Environment;
use Lorry\Views\Error;

class Internal extends Error {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		$this->message = gettext('Internal error.');
	}

}