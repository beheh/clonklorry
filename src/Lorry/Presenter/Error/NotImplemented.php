<?php

namespace Lorry\Presenter\Error;

use \Lorry\Presenter;

class NotImplemented extends Presenter\Error {

	protected function getMessage() {
		return 'Not implemented';
	}

}