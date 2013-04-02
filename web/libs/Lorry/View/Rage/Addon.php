<?php

class Lorry_View_Rage_Addon extends Lorry_View_Rage {

	private $addon;

	protected function render() {
		return $this->lorry->twig->render('list.twig');
	}

	protected function hasWildcard($wildcard) {
		//fetch addon object from persistence service
		if(($this->addon = $this->lorry->persistence->get('addon')->byName($wildcard)) !== false) {
			return true;
		}

		if(($this->addon = $this->lorry->persistence->get('addon')->byShort($wildcard)) !== false) {
			$this->redirect($this->addon->getName());
		}

		return false;
	}

	protected function renderWildcard() {
		$context = array();
		$context['addon_title'] = $this->addon->getTitle();

		return $this->lorry->twig->render('addon/release.twig', $context);
	}

}

