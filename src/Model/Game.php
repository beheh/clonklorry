<?php

namespace Lorry\Model;

use Lorry\Model;

class Game extends Model {

	public function __construct() {
		parent::__construct('game', array(
			'short' => 'varchar(16)',
			'title' => 'varchar(16)'
		));
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

	public function available() {
		return $this->fetchAll();
	}

	public function __toString() {
		return $this->getTitle().'';
	}

	public function forApi() {
		return array('id' => $this->getShort(), 'title' => $this->getTitle());
	}

}
