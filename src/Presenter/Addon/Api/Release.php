<?php

namespace Lorry\Presenter\Addon\Api;

use Lorry\ApiPresenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Release extends ApiPresenter {

	public function get($api_version, $gamename, $addonname, $version = 'latest') {
		if(intval($api_version) != 0) {
			throw new FileNotFoundException('this endpoint does not support api version '.$api_version);
		}

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
