<?php

namespace Lorry\Presenter\Auth;

use Lorry\Presenter;
use Opauth;

class Gateway extends Presenter {

	public function get() {
		require '../app/config/opauth.php';
		$current_state = $this->session->getState();
		if(!isset($_GET['state']) || !$current_state || $_GET['state'] != $current_state) {
			$state = $this->session->generateState();
			$config['Strategy']['Google']['state'] = $state;
			$config['Strategy']['Facebook']['state'] = $state;
			if(isset($_GET['identity'])) {
				$config['Strategy']['OpenID']['identity'] = filter_input(INPUT_GET, 'identity', FILTER_VALIDATE_URL);
			}
		}
		if($this->session->authenticated()) {
			$config['Strategy']['Google']['login_hint'] = $this->session->getUser()->getEmail();
		}
		new Opauth($config);
	}

}
