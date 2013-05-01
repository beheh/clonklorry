<?php

namespace Lorry\Presenters;

use Lorry\Presenter;

class Publish extends Presenter {

	protected function allow() {
		return true;
	}

	protected function render() {
		if($this->lorry->session->authenticated()) {
			return $this->lorry->twig->render('publish/list.twig');
		} else {
			return $this->lorry->twig->render('publish/tutorial.twig');
		}
	}

}

