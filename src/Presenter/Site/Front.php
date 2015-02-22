<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Front extends Presenter {
	public function get() {
		$release = $this->persistence->build('Release')->all()->order('timestamp', true)->byAnything();
		$addons = array();
		foreach($release as $release) {
			if(!$release->isReleased()) continue;
			$addon = $release->fetchAddon();
			$game = $addon->fetchGame();
			$addons[] = array(
				'title' => $addon->getTitle(),
				'short' => $addon->getShort(),
				'introduction' => $addon->getIntroduction(),
				'version' => $release->getVersion(),
				'game' => array(
					'title' => $game->getTitle(),
					'short' => $game->getShort())
			);
		}
		$this->context['new_addons'] = $addons;

		$this->context['new_user'] = $this->session->getFlag('new_user');

		$this->display('site/front.twig');
	}
}
