<?php

namespace Lorry\Presenter\Addon\Api;

use Lorry\ApiPresenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Games extends ApiPresenter {

	public function get($api_version) {
		if(intval($api_version) != 0) {
			throw new FileNotFoundException('this endpoint does not support api version '.$api_version);
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
