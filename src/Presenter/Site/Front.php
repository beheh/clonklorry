<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Front extends Presenter {
	public function get() {
		$release = ModelFactory::build('Release')->all()->order('timestamp', true)->byAnything();
		$addons = array();
		foreach($release as $release) {
			$addon = $release->fetchAddon();
			$game = $addon->fetchGame();
			$addons[] = array(
				'title' => $addon->getTitle(),
				'short' => $addon->getShort(),
				'description' => $addon->getDescription(),
				'version' => $release->getVersion(),
				'game' => array(
					'title' => $game->getTitle(),
					'short' => $game->getShort())
			);
		}
		$this->context['new_addons'] = $addons;

		$this->display('site/front.twig');
	}
}