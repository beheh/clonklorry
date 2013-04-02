<?php

class Lorry_View_Register extends Lorry_View {

	protected function render() {
		return $this->lorry->twig->render('register.twig');
	}

	protected function allow() {
		return true;
	}

}

