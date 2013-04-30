<?php

namespace Lorry\Views\Error;

use Lorry\Environment;
use Lorry\Views\Error;

class Forbidden extends Error {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		header('HTTP/1.1 403 Forbidden');
		if($this->lorry->session->authenticated()) {
			$this->setMessage(gettext('Access denied.'));
		} else {
			$this->setTitle(gettext('Access denied.'));
			$this->setMessage(gettext('You could try logging in.'));
		}
	}

}