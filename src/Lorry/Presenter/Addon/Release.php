<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;

class Release extends Presenter {

	public function get($gamename, $addonname, $version = 'latest') {
		$user = $this->session->getUser();

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

		$this->context['addon'] = array('title' => $addon->getTitle(), 'short' => $addon->getShort());
		$this->context['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());

		$this->context['developer'] = $owner->getUsername();
		$this->context['version'] = $release->getVersion();

		$this->context['addon_description'] = $addon->getDescription();
		$this->context['release_description'] = $release->getDescription();

		$this->context['dependencies'] = array();
		$dependencies = $addon->fetchDependencies();
		foreach($dependencies as $dependency) {
			$dependency_addon = $dependency->fetchAddon();
			if(!$dependency_addon)
				continue;
			$this->context['dependencies'][] = array('title' => $dependency_addon->getTitle(), 'short' => $dependency_addon->getShort());
		}

		$this->context['requirements'] = array();
		$requirements = $addon->fetchRequirements();
		foreach($requirements as $requirement) {
			$requirement_addon = $requirement->fetchRequired();
			if(!$requirement_addon) {
				continue;
			}
			$game = $requirement_addon->fetchGame();

			$this->context['requirements'][] = array(
				'title' => $requirement_addon->getTitle(),
				'short' => $requirement_addon->getShort(),
				'game' => $game->getShort());
		}

		$this->context['releaseday'] = strtr(gettext('%day% of %month% %year%'), array(
			'%day%' => $this->localisation->countedNumber('1'),
			'%month%' => $this->localisation->namedMonth('1'),
			'%year%' => '2013'));

		$this->context['website'] = $addon->getWebsite();
		$this->context['bugtracker'] = $addon->getBugtracker();

		$this->context['modify'] = $user && ($addon->getOwner() == $user->getId() || $user->isAdministrator());

		$this->display('addon/release.twig');
	}

}