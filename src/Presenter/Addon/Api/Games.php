<?php

namespace Lorry\Presenter\Addon\Api;

use Lorry\ApiPresenter;
use Lorry\ModelFactory;

class Games extends ApiPresenter {

	public function get() {
		$games = ModelFactory::build('Game')->byAnything();
		
		$result = array();
		$result['games'] = array();
		foreach($games as $game) {
			$result['games'][] = $game->forApi();
		}
		$this->display($result);
	}

}
