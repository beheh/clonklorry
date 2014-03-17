<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\ModelValueInvalidException;

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

		if(!isset($this->context['addontitle'])) {
			$this->context['addontitle'] = $addon->getTitle();
		}

		if(!isset($this->context['abbreviation'])) {
			$this->context['abbreviation'] = $addon->getAbbreviation();
		}

		if(isset($_GET['add'])) {
			$this->context['focus_release'] = true;
		}

		$games = ModelFactory::build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}

		if(!isset($this->context['game'])) {
			$game = $addon->fetchGame();
			$this->context['game'] = $game->getShort();
		}

		$this->display('publish/edit.twig');
	}

	public function post($id) {
		$this->security->requireLogin();

		$addon = $this->getAddon($id);

		if(isset($_GET['addon'])) {
			$errors = array();

			$title = trim(filter_input(INPUT_POST, 'title'));
			try {
				$addon->setTitle($title);
				$this->context['addontitle'] = $addon->getTitle();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Title is %s.'), $ex->getMessage());
				$this->context['addontitle'] = $title;
			}

			try {
				$game = ModelFactory::build('Game')->byShort(filter_input(INPUT_POST, 'game'));
				if(!$game) {
					throw new ModelValueInvalidException('invalid');
				}
				$this->context['game'] = $game->getShort();
				$addon->setGame($game->getId());
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Game is %s.'), $ex->getMessage());
			}

			$abbreviation = trim(filter_input(INPUT_POST, 'abbreviation'));
			try {
				$addon->setAbbreviation($abbreviation);
				$this->context['abbreviation'] = $addon->getAbbreviation();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Abbreviation is %s.'), $ex->getMessage());
				$this->context['abbreviation'] = $abbreviation;
			}

			if(empty($errors)) {
				if($addon->modified()) {
					$addon->save();
					$this->success('addon', gettext('Addon saved.'));
				}
			} else {
				$this->error('addon', implode('<br>', $errors));
			}
		}

		$this->get($id);
	}

}
