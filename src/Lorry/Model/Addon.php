<?php

namespace Lorry\Models;

use Lorry\Model;

class Addon extends Model {

	public function __construct() {
		parent::__construct('addon', array(
			'short' => 'string',
			'title' => 'string',
			'game' => 'int'));
	}

	public function setShort($short) {
		return $this->setValue('short', $short);
	}

	public function byShort($short) {
		return $this->byValue('short', $short);
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

	public function getGame() {
		return $this->getValue('game');
	}

	public function __toString() {
		return $this->getTitle() . '';
	}

}