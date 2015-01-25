<?php

namespace Lorry\Presenter\Redirect;

use Lorry\Presenter;

class Front extends Presenter\Redirect {

	public function getLocation() {
		return '/';
	}

}
