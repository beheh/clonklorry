<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\ModelValueInvalidException;

class Create extends Presenter {

	public function get() {
		$this->security->requireLogin();

		$games = ModelFactory::build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}

		if(!isset($this->context['game'])) {
			$this->context['game'] = filter_input(INPUT_GET, 'for');
		}

		$objects = array(gettext('Stippel'), gettext('Monster'), gettext('Wipf'), gettext('Stage'), gettext('Bridge'), gettext('Western'), 'Fantasy', 'Mars', gettext('Knight'), gettext('Magic'), gettext('Pressurewave'));
		$phrases = array(gettext('%s Reloaded'), gettext('Metal & %s'), gettext('%sfight'), gettext('%s pack'), gettext('%sparty'), gettext('Left 2 %s'), gettext('%sclonk'), gettext('%s clonks'), gettext('%s race'));
		$modifiers = array(gettext('%s Extreme'), gettext('Codename: %s'));
		$example = sprintf($phrases[array_rand($phrases)], $objects[array_rand($objects)]);
		if(!rand(0, 5)) {
			$example = sprintf($modifiers[array_rand($modifiers)], $example);
		}
		$this->context['exampletitle'] = $example;

		$this->display('publish/create.twig');
	}

	public function post() {
		$this->security->requireLogin();
		$this->security->requireValidState();

		$user = $this->session->getUser();

		$errors = array();


		$addon = ModelFactory::build('Addon');

		$addon->setOwner($user->getId());

		$title = filter_input(INPUT_POST, 'title');
		try {
			$this->context['addontitle'] = $title;
			$addon->setTitle($title);
			$this->context['title_valid'] = true;
		} catch(ModelValueInvalidException $ex) {
			$errors[] = sprintf(gettext('Title is %s.'), $ex->getMessage());
		}

		$type = filter_input(INPUT_POST, 'type');
		try {
			$this->context['type'] = $type;
		} catch(ModelValueInvalidException $ex) {
			$errors[] = sprintf(gettext('Type is %s.'), $ex->getMessage());
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

		$existing = ModelFactory::build('Addon')->all()->byTitle($title, $user->getId(), $game->getId());
		if(count($existing) > 0) {
			$errors[] = gettext('You have already created an addon with this title for this game.');
		}

		if(!empty($errors)) {
			$this->error('creation', implode('<br>', $errors));
		} else {
			$addon->save();
			$this->redirect('/publish?created='.$addon->getId());
		}

		$this->get();
	}

}
