<?php

namespace Lorry\Presenters\Publish;

use Lorry\Presenters\Publish;

class Add extends Publish {

	protected function allow() {
		return true;
	}

	protected function render() {
		return $this->lorry->twig->render('publish/add.twig');
	}

}