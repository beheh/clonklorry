<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Settings extends Presenter {

	public function get() {
		$this->twig->display('account/settings.twig');
	}

}