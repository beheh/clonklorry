<?php

namespace Lorry\Presenters;

use Lorry\Presenter;

class Contact extends Presenter {

	protected function allow() {
		return true;
	}

	protected function render() {
		return $this->lorry->twig->render('site/contact.twig');
	}

}