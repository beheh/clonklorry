<?php

namespace Lorry\Presenter\Api;

use Lorry\Presenter\Api\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Games extends Presenter {

	public function get($api_version) {
		if(intval($api_version) != 0) {
			throw new FileNotFoundException(sprintf(gettext('This endpoint does not support api version %d.'), $api_version));
		}

		$games = ModelFactory::build('Game')->byAnything();

		$result = array();
		$result['games'] = array();
		foreach($games as $game) {
			$result['games'][] = $game->forApi();
		}
		$this->display($result);
	}

}
