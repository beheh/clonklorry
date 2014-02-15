<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;
use Opauth;

class Gateway extends Presenter {

	public function get() {
		require '../app/config/opauth.php';
		$current_state = $this->session->getState();
		if(!isset($_GET['state']) || !$current_state || $_GET['state'] != $current_state) {
			$config['Strategy']['Google']['state'] = $this->session->generateState();
		}
		if($this->session->authenticated()) {
			$config['Strategy']['Google']['login_hint'] = $this->session->getUser()->getEmail();
		}
		new Opauth($config);
	}

}
