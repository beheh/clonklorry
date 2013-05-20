<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ModelFactory;

class Addon extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'owner' => 'string',
			'short' => 'string',
			'title' => 'string',
			'game' => 'int',
			'public' => 'boolean',
			'description' => 'text'));
	}

	public function setOwner($owner) {
		return $this->setValue('owner', $owner);
	}

	public function byOwner($owner, $private = false) {
		$constraints = array('owner' => $owner);
		if(!$private) {
			$constraints['public'] = true;
		}
		return $this->byValues($constraints);
	}

	public function getOwner() {
		return $this->getValue('owner');
	}

	public function fetchOwner() {
		return ModelFactory::build('User')->byId($this->getOwner());
	}

	public function setShort($short) {
		return $this->setValue('short', $short);
	}

	public function byShort($short, $game, $private = false) {
		$constraints = array('short' => $short, 'game' => $game);
		if(!$private) {
			$constraints['public'] = true;
		}
		return $this->byValues($constraints);
	}

	public function getShort() {
		return $this->getValue('short');
	}

	public function setTitle($title) {
		return $this->setValue('title', $title);
	}

	public function getTitle() {
		return $this->getValue('title');
	}

	public function setGame($game) {
		return $this->setValue('game', $game);
	}

	public function byGame($game, $private = false) {
		$constraints = array('game' => $game);
		if(!$private) {
			$constraints['public'] = true;
		}
		return $this->byValues($constraints);
	}

	public function getGame() {
		return $this->getValue('game');
	}

	public function fetchGame() {
		return ModelFactory::build('Game')->byId($this->getGame());
	}

	public function setPublic($public) {
		return $this->setValue('public', $public);
	}

	public function isPublic() {
		return $this->getValue('public');
	}

	public function setDescription($description) {
		return $this->setValue('description', $description);
	}

	public function getDescription() {
		return $this->getValue('description');
	}

	public function fetchRequirements() {
		return ModelFactory::build('Dependency')->all()->byAddon($this->getId());
	}

	public function fetchDependencies() {
		return ModelFactory::build('Dependency')->all()->byRequired($this->getId());
	}

	public function __toString() {
		return $this->getTitle().'';
	}

}