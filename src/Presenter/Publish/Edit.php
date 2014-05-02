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
		$this->context['addonid'] = $addon->getId();

		$this->context['title'] = $addon->getTitle();

		if(!isset($this->context['addontitle'])) {
			$this->context['addontitle'] = $addon->getTitle();
		}

		if(!isset($this->context['abbreviation'])) {
			$this->context['abbreviation'] = $addon->getAbbreviation();
		}

		if(isset($_GET['add'])) {
			$this->context['focus_version'] = true;
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

		$releases = ModelFactory::build('Release')->all()->order('version')->byAddon($addon->getId());
		$latest = ModelFactory::build('Release')->latest($addon->getId());
		$this->context['releases'] = array();
		foreach($releases as $release) {
			$this->context['releases'][$release->getId()] = array(
				'version' => $release->getVersion(),
				'released' => $release->isReleased(),
				'latest' => ($latest && $latest->getId() == $release->getId()),
				'scheduled' => $release->isScheduled());
		}

		$this->display('publish/edit.twig');
	}

	public function post($id) {
		$this->security->requireLogin();

		$this->security->requireValidState();

		$addon = $this->getAddon($id);

		if(isset($_POST['addon-submit'])) {
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

		if(isset($_POST['release-submit'])) {
			$version = ltrim(trim(filter_input(INPUT_POST, 'version')), 'v');

			$release = ModelFactory::build('Release');
			$release->setAddon($addon->getId());

			$errors = array();

			try {
				$release->setVersion($version);
				$version = $release->getVersion();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Version is %s.'), $ex->getMessage());
			}
			$this->context['version'] = $version;

			if(ModelFactory::build('Release')->byVersion($version, $addon->getId()) !== false) {
				$errors[] = gettext('Version already exists.');
			}

			if(empty($errors)) {
				$release->save();
				$this->redirect('/publish/'.$addon->getId().'/'.$release->getVersion());
			} else {
				$this->error('release', implode('<br>', $errors));
				$this->context['focus_version'] = true;
			}
		}

		$this->get($id);
	}

}
