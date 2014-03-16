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

			$addons = ModelFactory::build('Addon')->all()->byOwner($user->getId(), true);
			
			$this->context['addons'] = array();
			foreach($addons as $addon) {
				$user_addon = array();
				$user_addon['title'] = $addon->getTitle();
				$user_addon['short'] = $addon->getShort();
				$user_addon['id'] = $addon->getId();
				$game = $addon->fetchGame();
				if($game) {
					$user_addon['game'] = array('title' => $game->getTitle(), 'short' => $game->getShort());
				}
				if(!$addon->isPublic()) {
					$user_addon['year'] = 2013;
					$this->context['addons'][] = $user_addon;
				} else {
					$this->context['addons'][] = $user_addon;
				}
			}

			$this->display('publish/portal.twig');
		} else {
			$this->display('publish/greeter.twig');
		}
	}
}