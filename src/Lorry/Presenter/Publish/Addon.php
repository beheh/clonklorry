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

		$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId(), true);
		if(!$addon) {
			throw new FileNotFoundException('addon '.$addonname);
		}

		$this->context['title'] = strtr(gettext('Edit %addon%'), array('%addon%' => $addon->getTitle()));

		$this->context['addon_title'] = $addon->getTitle();
		$this->context['addon_short'] = $addon->getShort();
		$this->context['addon_short_placeholder'] = preg_replace('#[^a-z]#', '', strtolower($addon->getTitle()));

		$this->display('publish/addon.twig');
	}

	public function post($gamename, $addonname) {
		if(isset($_GET['change-details'])) {
			$title = filter_input(INPUT_POST, 'title');
			$short = urlencode(filter_input(INPUT_POST, 'short'));

			$game = ModelFactory::build('Game')->byShort($gamename);
			if(!$game) {
				throw new FileNotFoundException('game '.$gamename);
			}

			$addon = ModelFactory::build('Addon')->byShort($addonname, $game->getId(), true);
			if(!$addon) {
				throw new FileNotFoundException('addon '.$addonname);
			}

			$addon->setTitle($title);
			$addon->setShort($short);

			$addon->save();

			if($short != $addonname) {
				return $this->redirect($short, true);
			}
		}

		if(isset($_GET['add-version'])) {
			
		}

		$this->get($gamename, $addonname);
	}

}
