<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\ModelFactory;

class Game extends Presenter {

	public function get($name) {
		$game = ModelFactory::build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException('game '.$game);
		}

		$this->context['title'] = strtr(gettext('Addons for %game%'), array('%game%' => $game->getTitle()));
		$this->context['short'] = $game->getShort();

		$addons = ModelFactory::build('Addon')->all()->byGame($game->getId());
		$this->context['addons'] = array();
		foreach($addons as $addon) {
			$this->context['addons'][] = array(
				'title' => $addon->getTitle(),
				'short' => $addon->getShort()
			);
		}


		$this->display('addon/game.twig');
	}

}