<?php

class Lorry_View_About extends Lorry_View {

	protected function render() {
		$this->redirect($this->lorry->config->baseUrl.'about/lorry');
	}

	protected function allow() {
		return true;
	}
}

?>
