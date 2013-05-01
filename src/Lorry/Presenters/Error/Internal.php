<?php

namespace Lorry\Presenters\Error;

use Lorry\Environment;
use Lorry\Presenters\Error;

class Internal extends Error {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		$this->message = gettext('Internal error.');
	}

}