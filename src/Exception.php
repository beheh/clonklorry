<?php

namespace Lorry;

use Exception as PHPException;

abstract class Exception extends PHPException {
	public function getPresenter() {
		return 'Error';
	}
}
