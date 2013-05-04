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

		$context = array();

		$context['addons_for_game'] = strtr(gettext('Addons for %game%'), array('%game%' => $game->getName()));

		$this->twig->display('addon/game.twig', $context);
	}

}