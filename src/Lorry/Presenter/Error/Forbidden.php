<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class Forbidden extends Presenter\Error {
	public function get() {
		echo 'Forbidden';
	}

}