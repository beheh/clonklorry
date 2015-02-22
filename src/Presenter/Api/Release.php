<?php

namespace Lorry\Presenter\Api;

use Lorry\Presenter\Api\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Release extends Presenter {

	public function get($api_version, $gamename, $addonname, $version = 'latest') {
		if(intval($api_version) != 0) {
			throw new FileNotFoundException(sprintf(gettext('This endpoint does not support api version %d.'), $api_version));
		}

		$game = $this->persistence->build('Game')->byShort($gamename);
		if(!$game) {
			throw new FileNotFoundException(gettext('Game does not exist.'));
		}

		$addon = $this->persistence->build('Addon')->byShort($addonname, $game->getId());
		if(!$addon) {
			$addon = $this->persistence->build('Addon')->byAbbreviation($addonname, $game->getId());
			if($addon) {
				return $this->redirect('/addons/'.$game->getShort().'/'.$addon->getShort());
			}
			throw new FileNotFoundException(gettext('Addon does not exist.'));
		}

		if($version == 'latest') {
			$release = $this->persistence->build('Release')->latest($addon->getId());
		} else {
			$release = $this->persistence->build('Release')->byVersion($version, $addon->getId());
		}
		if(!$release || !$release->isScheduled()) {
			throw new FileNotFoundException(gettext('Release does not exist.'));
		}

		$result = array();
		$result['addon'] = $addon->forApi();
		$this->display($result);
	}

}
