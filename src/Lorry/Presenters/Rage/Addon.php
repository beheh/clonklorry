<?php

namespace Lorry\Presenters\Rage;

use Lorry\Presenters\Rage;

class Addon extends Rage {

	private $addon;

	protected function render() {
		return $this->redirect($this->lorry->config->base.'rage');
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
		$context['title'] = $this->addon->getTitle();
		$context['team'] = strtr(_('%teamname% team'), array('%teamname%' => 'CMC'));
		$context['version'] = '1.5';
		$context['releaseday'] = strtr(gettext('%day% of %month% %year%'), array('%day%' => $this->lorry->localisation->countedNumber(1), '%month%' => $this->lorry->localisation->namedMonth(11), '%year%' => '2012'));
		//$context['releaseday'] = '1st of November 2012';

		//return $this->lorry->twig->render('addon/release.twig', $context);

		return $this->lorry->twig->render('addon/countdown.twig', $context);
	}

}

