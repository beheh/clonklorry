<?php

namespace Lorry\Exception;

use Lorry\Exception;

class OutputCompleteException extends Exception {
	public function getPresenter() {
		return '';
	}
}
