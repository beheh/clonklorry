<?php

namespace Lorry\Presenter\Account;

use \Lorry\Presenter;

class Logout extends Presenter {

	public function get() {
		$this->redirect();
	}

}