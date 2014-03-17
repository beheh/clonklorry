<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;

class Edit extends Presenter {

	private function getAddon($id) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if($addon->getOwner() != $this->session->getUser()->getId()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	public function get($id) {
		$this->security->requireLogin();

		$addon = $this->getAddon($id);

		$this->context['title'] = $addon->getTitle();

		$this->context['addontitle'] = $addon->getTitle();

		$this->context['abbreviation'] = $addon->getAbbreviation();
		
		if(isset($_GET['add'])) {
			$this->context['focus_release'] = true;
		}
		
		$games = ModelFactory::build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}

		$game = $addon->fetchGame();
		$this->context['game'] = $game->getShort();

		$this->display('publish/edit.twig');
	}

	public function post($id) {
		$this->security->requireLogin();

		$addon = $this->getAddon($id);

		$this->get($id);
	}

}
