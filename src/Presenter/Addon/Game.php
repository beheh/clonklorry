<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;

class Game extends Presenter {

	public function get($name) {
		$game = $this->persistence->build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException('game '.$game);
		}

		$this->context['title'] = $game->getTitle();
		$this->context['game'] = $game->getTitle();
		$this->context['short'] = $game->getShort();

		$query = $this->persistence->build('Release')->all();

		/*$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
		$reverse = isset($_GET['reverse']) && $_GET['reverse'] == 1 ? true : false;
		switch($sort) {
			case 'title':
				$query = $query->order('title', $reverse);
				break;
			case 'rating':
				//@TODO sort by rating
				break;
			case 'date':
			default:
				$sort = 'date';
				$query = $query->order('updated', !$reverse);
				break;
		}
		$this->context['sort'] = $sort;
		$this->context['reverse'] = $reverse;*/

		$releases = $query->byGame($game->getId());
		$this->context['addons'] = array();
		foreach($releases as $release) {
			$addon = $release->fetchAddon();
			$this->context['addons'][] = array(
				'title' => $addon->getTitle(),
				'version' => $release->getVersion(),
				'short' => $addon->getShort(),
				'introduction' => $addon->getIntroduction()
			);
		}

		$this->display('addon/game.twig');
	}

}
