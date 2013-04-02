<?php

class Lorry_View_Openclonk extends Lorry_View {

	protected function render() {
		return $this->lorry->twig->render('gamefront/basic.twig', $context = array('heading' => gettext('OpenClonk')));
	}

	protected function allow() {
		return true;
	}

}

