<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Create extends Presenter {

	public function get() {
		$this->security->requireLogin();

		$games = ModelFactory::build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}

		$objects = array('Stippel', 'Monster', 'Etagen', 'Brückensegmente', 'Western', 'Fantasy', 'Mars', 'Ritter', 'Magie');
		$phrases = array('%s Reloaded', '%s Extreme', 'Codename: %s', 'Metall & %s', '%skampf', '%s Pack', '%s Party', 'Left 2 %s', '%sclonk', '%srennen', '%s	arena');
		$this->context['exampletitle'] = sprintf($phrases[array_rand($phrases)], $objects[array_rand($objects)]);

		$this->display('publish/create.twig');
	}

	public function post() {
		$this->security->requireLogin();

		if(false) {
			$this->redirect('/publish');
		}

		$this->get();
	}

}
