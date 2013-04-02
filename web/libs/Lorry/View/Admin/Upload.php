<?php

class Lorry_View_Admin_Upload extends Lorry_View_Admin {

	protected function render() {
		$response = $this->lorry->cdn->release('ModernCombat.c4d');
		return print_r($response, true);
	}

}