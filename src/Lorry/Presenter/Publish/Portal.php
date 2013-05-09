<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Portal extends Presenter {

	private $selected = '';

	public function get() {
		if($this->session->authenticated()) {

			$games = ModelFactory::build('Game')->any();

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