<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Release extends Presenter {

	public function get($gamename, $addonname, $version = 'latest') {
		$game = ModelFactory::build('Game')->byShort($gamename);
		if(!$game) {
			throw new FileNotFoundException('game '.$gamename);
		}

		$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId());
		if(!$addon) {
			throw new FileNotFoundException('addon '.$addonname);
		}

		$owner = ModelFactory::build('User')->byId($addon->getOwner());

		if($version == 'latest') {
			$release = ModelFactory::build('Release')->latest($addon->getId());
		} else {
			$release = ModelFactory::build('Release')->byVersion($version, $addon->getId());
		}
		if(!$release) {
			throw new FileNotFoundException('release with version '.$version);
		}

		$this->context['title'] = $addon->getTitle();
		$this->context['developer'] = $owner->getUsername();
		$this->context['version'] = $release->getVersion();

		$this->context['addon_description'] = $addon->getDescription();
		$this->context['release_description'] = $release->getDescription();

		$this->display('addon/release.twig');
	}

}