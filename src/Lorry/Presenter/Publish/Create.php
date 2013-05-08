<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Create extends Presenter {

	public function get() {
		$this->security->requireLogin();

		$games = ModelFactory::build('Game')->any();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][] = array(
				'selected' => isset($_GET['for']) && $game->getShort() == $_GET['for'],
				'short' => $game->getShort(),
				'title' => $game->getTitle(),
			);
		}

		$this->display('publish/create.twig');
	}

}