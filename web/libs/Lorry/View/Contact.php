<?php

class Lorry_View_Contact extends Lorry_View {

	protected function render() {
		return $this->lorry->twig->render('contact.twig');
	}

	protected function allow() {
		return true;
	}

}

