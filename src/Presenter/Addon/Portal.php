<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Portal extends Presenter {

	public function get() {
		$games = ModelFactory::build('Game')->byAnything();

		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][] = array(
				'short' => $game->getShort(),
				'title' => $game->getTitle(),
			);
		}

		$this->display('addon/portal.twig');
	}

}