<?php

namespace Lorry\Presenter\Api;

use Lorry\Presenter\Api\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\ModelFactory;

class Game extends Presenter {

	public function get($api_version, $name) {
		if(intval($api_version) != 0) {
			throw new FileNotFoundException(sprintf(gettext('This endpoint does not support api version %d.'), $api_version));
		}

		$game = ModelFactory::build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException(gettext('Game does not exist.'));
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
