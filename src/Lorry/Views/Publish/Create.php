<?php

namespace Lorry\Views\Publish;

use Lorry\Views\Publish;

class Create extends Publish {

	protected function allow() {
		return true;
	}

	protected function render() {
		return $this->twig->render('publish/create.twig');
	}

}