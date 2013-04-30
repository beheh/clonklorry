<?php

namespace Lorry\Views;

use Lorry\View;

class Rage extends View {

	protected final function allow() {
		return true;
	}

	protected function render() {
		$context['title'] = 'Clonk Rage';
		return $this->lorry->twig->render('store/list.twig', $context);
	}

}