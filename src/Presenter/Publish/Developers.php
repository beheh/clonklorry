<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception\ModelValueInvalidException;

class Developers extends Presenter {

	public function get() {
		$games = $this->persistence->build('Game')->all()->byAnything();
		$this->context['games'] = array();
		foreach($games as $game) {
			$this->context['games'][$game->getShort()] = array('title' => $game->getTitle());
		}

		if(!isset($this->context['game'])) {
			$this->context['game'] = filter_input(INPUT_GET, 'create');
		}

		if(isset($_GET['create']) && !isset($this->context['focus_title']))  {
			$this->context['focus_title'] = true;
		}

		$singular_objects = array(gettext('Wipf'), gettext('Monster'), gettext('Bridge'), gettext('Tower'), gettext('Stage'), gettext('Pressurewave'), gettext('Fantasy'), gettext('Stippel'), gettext('Quake'));
		$singular_phrases = array(gettext('%s pack'), gettext('%s of despair'), gettext('%s Infinity'), gettext('%sarena'), gettext('%s fight'), gettext('%s race'), gettext('%s Party'), gettext('%s Valley'), gettext('%s lands'), gettext('%s defense'));
		$plural_objects = array(gettext('Wipfs'), gettext('Monsters'), gettext('Bridges'), gettext('Towers'), gettext('Pressurewaves'), gettext('Flints'), gettext('Knights'), gettext('Clonks'), gettext('Stippels'));
		$plural_phrases = array(gettext('Metal & %s'), gettext('Left 2 %s'), gettext('%s descend from the top'), gettext('%s rise from the bottom'));

		if(!rand(0, 5)) {
			$example = sprintf($plural_phrases[array_rand($plural_phrases)], $plural_objects[array_rand($plural_objects)]);
		}
		else {
			$example = sprintf($singular_phrases[array_rand($singular_phrases)], $singular_objects[array_rand($singular_objects)]);
		}

		$modifiers = array(gettext('%s Extreme'), gettext('Codename: %s'), gettext('%s Remake'), gettext('%s Reloaded'));
		if(!rand(0, 5)) {
			$example = sprintf($modifiers[array_rand($modifiers)], $example);
		}
		
		$this->context['exampletitle'] = $example;

		$this->display('publish/developers.twig');
	}

	public function post() {
		$this->security->requireLogin();
		$this->security->requireValidState();

		$user = $this->session->getUser();

		$errors = array();


		$addon = $this->persistence->build('Addon');

		$addon->setOwner($user->getId());

		$title = filter_input(INPUT_POST, 'title');
		try {
			$this->context['addontitle'] = $title;
			$addon->setTitle($title);
			$this->context['focus_title'] = false;
		} catch(ModelValueInvalidException $ex) {
			$errors[] = sprintf(gettext('Title is %s.'), $ex->getMessage());
			$this->context['focus_title'] = true;
		}

		$type = filter_input(INPUT_POST, 'type');
		try {
			$this->context['type'] = $type;
		} catch(ModelValueInvalidException $ex) {
			$errors[] = sprintf(gettext('Type is %s.'), $ex->getMessage());
		}

		try {
			$game = $this->persistence->build('Game')->byShort(filter_input(INPUT_POST, 'game'));
			if(!$game) {
				throw new ModelValueInvalidException('invalid');
			}
			$this->context['game'] = $game->getShort();
			$addon->setGame($game->getId());
		} catch(ModelValueInvalidException $ex) {
			$errors[] = sprintf(gettext('Game is %s.'), $ex->getMessage());
		}

		$existing = $this->persistence->build('Addon')->all()->byTitle($title, $user->getId(), $game->getId());
		if(count($existing) > 0) {
			$errors[] = gettext('You have already created an addon with this title for this game.');
			$this->context['focus_title'] = true;
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
