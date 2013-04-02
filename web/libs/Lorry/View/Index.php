<?php

class Lorry_View_Index extends Lorry_View {

	protected function render() {
		if((isset($_GET['greet']) && $_GET['greet'] == 1) || ((!isset($_COOKIE['greeted']) || !$_COOKIE['greeted']) && (!isset($_GET['greet']) || $_GET['greet'] == 0))) {
			if(!isset($_COOKIE['greeted']) || !$_COOKIE['greeted']) {
				setcookie('greeted', 1);
			}
			return $this->lorry->twig->render('greeter.twig');
		} else {
			return $this->lorry->twig->render('storefront.twig');
		}
	}

	protected function allow() {
		return true;
	}

}

