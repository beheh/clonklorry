<?php

namespace Lorry\Presenter\Addon;

use Lorry\ApiPresenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class ReleaseApi extends ApiPresenter {

	public function get($gamename, $addonname, $version = 'latest') {
		$game = ModelFactory::build('Game')->byShort($gamename);
		if(!$game) {
			throw new FileNotFoundException('game does not exist');
		}

		$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId());
		if(!$addon) {
			$addon = ModelFactory::build('Addon')->byAbbreviation($addonname, $game->getId());
			if($addon) {
				return $this->redirect('/addons/'.$game->getShort().'/'.$addon->getShort());
			}
			throw new FileNotFoundException('addon does not exist');
		}

		if($version == 'latest') {
			$release = ModelFactory::build('Release')->latest($addon->getId());
		} else {
			$release = ModelFactory::build('Release')->byVersion($version, $addon->getId());
		}
		if(!$release || !$release->isScheduled()) {
			throw new FileNotFoundException('release with version '.$version);
		}

		$result = array();
		$result['addon'] = $addon->forApi();
		$this->display($result);
	}

}