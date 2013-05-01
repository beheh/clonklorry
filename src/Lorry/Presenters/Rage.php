<?php

namespace Lorry\Presenters;

use Lorry\Presenter;

class Rage extends Presenter {

	protected final function allow() {
		return true;
	}

	protected function render() {
		$context['title'] = 'Clonk Rage';
		return $this->lorry->twig->render('store/list.twig', $context);
	}

}