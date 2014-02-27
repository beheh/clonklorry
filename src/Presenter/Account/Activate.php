<?php

namespace Lorry\Presenter\Account;

use Lorry\Presenter;

class Activate extends Presenter {

	public function get() {
		$user = $this->session->getUser();
		$user->activate();
		$user->save();
	}

}
