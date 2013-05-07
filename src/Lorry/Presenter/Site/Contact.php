<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Contact extends Presenter {

	public function get() {
		$this->display('site/contact.twig');
	}

	public function post() {
		$this->get();
	}

}