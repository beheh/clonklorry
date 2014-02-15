<?php

namespace Lorry\Presenter\Addon;

use Lorry\Presenter;
use Lorry\Exception\FileNotFoundException;
use Lorry\ModelFactory;

class Game extends Presenter {

	public function get($name) {
		$game = ModelFactory::build('Game')->byShort($name);
		if(!$game) {
			throw new FileNotFoundException('game '.$game);
		}

		$this->context['title'] = $game->getTitle();
		$this->context['game'] = strtr(gettext('Addons for %game%'), array('%game%' => $game->getTitle()));
		$this->context['short'] = $game->getShort();

		$query = ModelFactory::build('Addon')->all();

		$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
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
		$this->context['reverse'] = $reverse;

		$addons = $query->byGame($game->getId());
		$this->context['addons'] = array();
		foreach($addons as $addon) {
			$this->context['addons'][] = array(
				'title' => $addon->getTitle(),
				'short' => $addon->getShort(),
				'description' => $addon->getDescription(),
			);
		}

		$this->display('addon/game.twig');
	}

}