<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ModelFactory;

class Addon extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'owner' => 'string',
			'short' => 'string',
			'abbreviation' => 'string',
			'title' => 'string',
			'game' => 'int',
			'public' => 'boolean',
			'description' => 'text',
			'website' => 'url',
			'bugtracker' => 'url'));
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
		$this->validateString($short, 4, 30);
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

	public function setAbbreviation($abbreviation) {
		return $this->setValue('abbreviation', $abbreviation);
	}

	public function getAbbreviation() {
		return $this->getValue('abbreviation');
	}

	public function byAbbreviation($abbreviation, $game) {
		$constraints = array('abbreviation' => $abbreviation, 'game' => $game, 'public' => true);
		return $this->byValues($constraints);
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

	public function setWebsite($website) {
		return $this->setValue('website', $website);
	}

	public function getWebsite() {
		return $this->getValue('website');
	}

	public function setBugtracker($bugtracker) {
		return $this->setValue('bugtracker', $bugtracker);
	}

	public function getBugtracker() {
		return $this->getValue('bugtracker');
	}

	public function __toString() {
		return $this->getTitle().'';
	}

}