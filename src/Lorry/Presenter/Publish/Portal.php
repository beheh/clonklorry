<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Portal extends Presenter {

	private $selected = '';

	public function get() {
		if($this->session->authenticated()) {
			$this->security->requireLogin();
			$user = $this->session->getUser();

			$games = ModelFactory::build('Game')->byAnything();

			if(empty($this->selected)) {
				if(isset($_GET['for'])) {
					$this->selected = $_GET['for'];
				}
			}

			$this->context['games'] = array();
			foreach($games as $game) {
				$this->context['games'][] = array(
					'selected' => $game->getShort() == $this->selected,
					'short' => $game->getShort(),
					'title' => $game->getTitle(),
				);
			}

			if(isset($_GET['for'])) {
				$this->context['focus'] = 'title';
			}

			$addons = ModelFactory::build('Addon')->all()->byOwner($user->getId());
			$this->context['addons_released'] = array();
			foreach($addons as $addon) {
				$user_addon = array();
				$user_addon['title'] = $addon->getTitle();
				$game = ModelFactory::build('Game')->byId($addon->getGame());
				if($game) {
					$user_addon['url'] = $this->config->get('base').'/addons/'.$game->getShort().'/'.$addon->getShort();
				}
				$this->context['addons_released'][] = $user_addon;
			}

			$this->display('publish/portal.twig');
		} else {
			$this->display('publish/greeter.twig');
		}
	}

	public function post() {
		$game_short = filter_input(INPUT_POST, 'game');
		$game = ModelFactory::build('Game')->byShort($game_short);
		if($game) {
			$this->selected = $game_short;
		} else {
			$this->error('create', gettext('Invalid game.'));
		}

		$title = filter_input(INPUT_POST, 'title');
		$this->context['addon_title'] = $title;

		$this->get();
	}

}