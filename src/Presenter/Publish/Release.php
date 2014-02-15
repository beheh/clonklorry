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

		$this->context['game'] = array(
			'short' => $game->getShort(),
			'title' => $game->getTitle());

		$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId(), true);
		if(!$addon) {
			throw new FileNotFoundException('addon '.$addonname);
		}

		$release = ModelFactory::build('Release')->byVersion($version, $addon->getId());
		if(!$release) {
			throw new FileNotFoundException('release '.$version);
		}

		$this->context['addon'] = array(
			'title' => $addon->getTitle(),
			'short' => $addon->getShort(),
			'abbrevation' => $addon->getAbbreviation(),
			'description' => $addon->getDescription()
		);

		$this->context['release'] = array(
			'version' => $release->getVersion(),
			'description' => $release->getDescription(),
			'release' => false
		);

		$timestamp = $release->getTimestamp();
		if(time() >= $timestamp) {
			$this->context['release']['release'] = true;
			$this->context['release']['date'] = date('d-m-Y', $timestamp);
			$this->context['release']['time'] = date('H:i', $timestamp);
		}

		$datetime = new DateTime('tomorrow 12:00');
		$this->context['datetime'] = $datetime->format('Y-m-d\TH:i:s');
		$datetime = new DateTime();
		$this->context['current_datetime'] = $datetime->format('Y-m-d\TH:i:s');
		$this->display('publish/release.twig');
	}

}