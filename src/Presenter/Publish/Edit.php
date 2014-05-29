<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Model\User;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\ModelValueInvalidException;

class Edit extends Presenter {

	public static function getAddon($id, User $user) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if($addon->getOwner() != $user->getId()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	public function get($id) {
		$this->security->requireLogin();

		$addon = Edit::getAddon($id, $this->session->getUser());
		$this->context['addonid'] = $addon->getId();

		$this->context['title'] = sprintf(gettext('Edit %s'), $addon->getTitle());

		if(!isset($this->context['addontitle'])) {
			$this->context['addontitle'] = $addon->getTitle();
		}

		if(!isset($this->context['short'])) {
			$this->context['short'] = $addon->getShort();
		}
		$title = $addon->getTitle();
		$maintitle = strstr($title, ':');
		$cleantitle = $maintitle ? $maintitle : $title;
		$this->context['short_proposal'] = preg_replace('/[^a-z0-9]/', '', strtolower($cleantitle));

		if(!isset($this->context['abbreviation'])) {
			$this->context['abbreviation'] = $addon->getAbbreviation();
		}

		if(!isset($this->context['short'])) {
			$this->context['short'] = $addon->getShort();
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

		/* Presentation */

		if(!isset($this->context['introduction'])) {
			$this->context['introduction'] = $addon->getIntroduction();
		}

		if(!isset($this->context['description'])) {
			$this->context['description'] = $addon->getDescription();
		}

		if(!isset($this->context['website'])) {
			$this->context['website'] = $addon->getWebsite();
		}

		if(!isset($this->context['bugtracker'])) {
			$this->context['bugtracker'] = $addon->getBugtracker();
		}


		/* Releases */

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

		$addon = Edit::getAddon($id, $this->session->getUser());

		if(isset($_POST['addon-form'])) {
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

			$url = trim(strtolower(filter_input(INPUT_POST, 'url')));
			try {
				$addon->setShort($url);
				$this->context['short'] = $addon->getShort();
			} catch(ModelValueInvalidException $ex) {
				$errors[] = sprintf(gettext('Url is %s.'), $ex->getMessage());
				$this->context['short'] = $url;
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

		if(isset($_POST['presentation-form'])) {
			$errors = array();

			$introduction = trim(filter_input(INPUT_POST, 'introduction'));
			try {
				$addon->setIntroduction($introduction);
			} catch(ModelValueInvalidException $ex) {
				$this->context['introduction'] = $introduction;
				$errors[] = sprintf(gettext('Introdution is %s.'), $ex->getMessage());
			}

			$description = trim(filter_input(INPUT_POST, 'description'));
			try {
				$addon->setDescription($description);
			} catch(ModelValueInvalidException $ex) {
				$this->context['description'] = $description;
				$errors[] = sprintf(gettext('Description is %s.'), $ex->getMessage());
			}

			$website = trim(filter_input(INPUT_POST, 'website-url'));
			try {
				$addon->setWebsite($website);
			} catch(ModelValueInvalidException $ex) {
				$this->context['website'] = $website;
				$errors[] = sprintf(gettext('Website is %s.'), $ex->getMessage());
			}

			$bugtracker = trim(filter_input(INPUT_POST, 'bugtracker-url'));
			try {
				$addon->setBugtracker($bugtracker);
			} catch(ModelValueInvalidException $ex) {
				$this->context['bugtracker'] = $bugtracker;
				$errors[] = sprintf(gettext('Bugtracker is %s.'), $ex->getMessage());
			}

			if(empty($errors)) {
				if($addon->modified()) {
					$addon->save();
					$this->success('presentation', gettext('Presentation saved.'));
				}
			} else {
				$this->error('presentation', implode('<br>', $errors));
			}
		}

		if(isset($_POST['release-form'])) {
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
