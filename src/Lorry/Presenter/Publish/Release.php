<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use DateTime;

class Release extends Presenter {

	public function get($gamename, $addonname, $version) {
		$this->security->requireLogin();

		$game = ModelFactory::build('Game')->byShort($gamename);
		if(!$game) {
			throw new FileNotFoundException('game '.$gamename);
		}

		$this->context['game_short'] = $game->getShort();

		$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId(), true);
		if(!$addon) {
			throw new FileNotFoundException('addon '.$addonname);
		}

		$this->context['addon_title'] = $addon->getTitle();
		$this->context['addon_short'] = $addon->getShort();
		$this->context['addon_abbreviation'] = $addon->getAbbreviation();

		$datetime = new DateTime('tomorrow 12:00');
		$this->context['datetime'] =  $datetime->format('Y-m-d\TH:i:s');
		$datetime = new DateTime();
		$this->context['current_datetime'] = $datetime->format('Y-m-d\TH:i:s');
		$this->display('publish/release.twig');
	}

}