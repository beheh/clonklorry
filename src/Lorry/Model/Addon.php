<?php

namespace Lorry\Model;

use Lorry\Model;

class Addon extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'owner' => 'string',
			'short' => 'string',
			'title' => 'string',
			'game' => 'int'));
	}

	public function setOwner($owner) {
		return $this->setValue('owner', $owner);
	}

	public function byOwner($owner) {
		return $this->byValue('owner', $owner);
	}

	public function getOwner() {
		return $this->getValue('owner');
	}

	public function setShort($short) {
		return $this->setValue('short', $short);
	}

	public function byShort($short, $game) {
		return $this->byValues(array('short' => $short, 'game' => $game));
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

	public function byGame($game) {
		return $this->byValue('game', $game);
	}

	public function getGame() {
		return $this->getValue('game');
	}

	public function __toString() {
		return $this->getTitle().'';
	}

}