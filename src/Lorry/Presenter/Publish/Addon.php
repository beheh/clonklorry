<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Addon extends Presenter {

	public function get($gamename, $addonname) {
		$this->security->requireLogin();
		$user = $this->session->getUser();

		$game = ModelFactory::build('Game')->byShort($gamename);
		if(!$game) {
			throw new FileNotFoundException('game '.$gamename);
		}

		$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId());
		if(!$addon) {
			throw new FileNotFoundException('addon '.$addonname);
		}

		$this->context['addon_title'] = $addon->getTitle();


		$this->display('publish/addon.twig');
	}

}
