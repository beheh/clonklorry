<?php

namespace Lorry\Model;

use Lorry\Model;

class Game extends Model {

	public function __construct() {
		parent::__construct('game', array(
			'name' => 'varchar(16)',
			'short' => 'varchar(16)'));
	}

	public function getName() {
		return $this->getValue('name');
	}

	public function setName($name) {
		return $this->setValue('name', $name);
	}

	public function getShort() {
		return $this->getValue('short');
	}

	public function setShort($short) {
		return $this->setValue('short', $short);
	}

	public function byShort($name) {
		return $this->byValue('short', $name);
	}

	public function __toString() {
		return $this->getUsername().'';
	}

}