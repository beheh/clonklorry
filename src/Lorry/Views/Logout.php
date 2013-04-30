<?php

namespace Lorry\Views;

use Lorry\View;

class Logout extends View {

	protected function allow() {
		return true;
	}

	protected function render() {
		$this->lorry->session->logout();
		return $this->redirect($this->lorry->config->base.'login?logout=true');
	}

}