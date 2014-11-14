<?php

namespace Lorry\Presenter\Addon\Api;

use Lorry\ApiPresenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\ModelFactory;

class Game extends ApiPresenter {

	public function get($name) {
		$game = ModelFactory::build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException('game does not exist');
		}

		$query = ModelFactory::build('Release')->all();

		$releases = $query->byGame($game->getId());

		$addons = array();
		foreach($releases as $release) {
			$addons[] = $release->fetchAddon()->forApi();
		}

		$result = array();
		$result['addons'] = $addons;
		$this->display($result);
	}

}
