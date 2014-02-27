<?php

namespace Lorry\Exception;

use Lorry\Exception;

class FileNotFoundException extends Exception {

	public function getPresenter() {
		return 'Error\FileNotFound';
	}

}
