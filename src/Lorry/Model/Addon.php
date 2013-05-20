<?php

namespace Lorry\Model;

use Lorry\Model;

class Addon extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'owner' => 'string',
			'short' => 'string',
			'title' => 'string',
			'game' => 'int',
			'public' => 'boolean'));
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

	public function setPublic($public) {
		return $this->setValue('public', $public);
	}

	public function isPublic() {
		return $this->getValue('public');
	}

	public function __toString() {
		return $this->getTitle().'';
	}

}