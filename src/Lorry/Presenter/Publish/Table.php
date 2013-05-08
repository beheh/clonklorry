<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Table extends Presenter {

	public function get() {
		if($this->session->authenticated()) {
			
			$games = ModelFactory::build('Game')->any();
			$this->context['games'] = array();
			foreach($games as $game) {
				$this->context['games'][] = array(
					'selected' => isset($_GET['for']) && $game->getShort() == $_GET['for'],
					'short' => $game->getShort(),
					'title' => $game->getTitle(),
				);
			}

			if(isset($_GET['for'])) {
				$this->context['focus'] = 'title';
			}

			$this->display('publish/table.twig');
		} else {
			$this->display('publish/greeter.twig');
		}
	}

}