<?php

class Lorry_View_Publish extends Lorry_View {

	protected function render() {
		if($this->lorry->session->authenticated()) {
			return $this->lorry->twig->render('publish/index.twig');
		} else {
			return $this->lorry->twig->render('publish/demo.twig');
		}
	}

	protected function allow() {
		return true;
	}

}

