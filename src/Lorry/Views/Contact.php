<?php

namespace Lorry\Views;

use Lorry\View;

class Contact extends View {

	protected function allow() {
		return true;
	}

	protected function render() {
		return $this->lorry->twig->render('site/contact.twig');
	}

}