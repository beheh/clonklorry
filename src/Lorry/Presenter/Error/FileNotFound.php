<?php

namespace Lorry\Presenter\Error;

use Lorry\Presenter;

class FileNotFound extends Presenter\Error {
	public function get() {
		echo 'File not found';
	}

}