<?php

class Lorry_View_Logout extends Lorry_View {

	protected function render() {
		$this->lorry->session->end();
		return $this->redirect($this->lorry->config->baseUrl);
	}

	protected function allow() {
		return true;
	}

}

