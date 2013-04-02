<?php

class Lorry_View_Rage extends Lorry_View {

	protected function render() {
		return $this->lorry->twig->render('gamefront/advanced.twig', $context = array('heading' => gettext('Clonk Rage')));
	}

	protected function allow() {
		return true;
	}

}

