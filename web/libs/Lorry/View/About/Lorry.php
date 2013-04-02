<?php

class Lorry_View_About_Lorry extends Lorry_View_About {

	protected function render() {
		return $this->lorry->twig->render('about.twig');
	}

}

