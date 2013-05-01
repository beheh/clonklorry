<?php

namespace Lorry\Presenters\Publish;

use Lorry\Presenters\Publish;

class Addon extends Publish {

	private $addon;

	protected function render() {
		$datetime = new \DateTime('tomorrow 12:00');
		return $this->lorry->twig->render('publish/release.twig', array('datetime' => $datetime->format('Y-m-d\\TH:i')));
		//return $this->redirect($this->lorry->config->base.'publish');
	}

	protected function hasWildcard($wildcard) {
		//fetch addon object from persistence service
		/*if(($this->addon = $this->lorry->persistence->get('addon')->byName($wildcard)) !== false) {
			return true;
		}

		if(($this->addon = $this->lorry->persistence->get('addon')->byShort($wildcard)) !== false) {
			$this->redirect($this->addon->getName());
		}*/

		return false;
	}

	protected function renderWildcard() {
		$context = array();
		$context['addon_title'] = $this->addon->getTitle();

		return $this->lorry->twig->render('publish/addon.twig', $context);
	}

}

