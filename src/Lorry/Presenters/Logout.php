<?php

namespace Lorry\Presenters;

use Lorry\Presenter;

class Logout extends Presenter {

	protected function allow() {
		return true;
	}

	protected function render() {
		$this->lorry->session->logout();
		return $this->redirect($this->lorry->config->base.'login?logout=true');
	}

}