<?php

class Lorry_View_About_Clonk extends Lorry_View_About {

	protected function render() {
		//throw new Exception('fail');
		return $this->lorry->twig->render('welcome.twig');
	}
}

