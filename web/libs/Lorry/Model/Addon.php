<?php

class Lorry_Model_Addon extends Lorry_Model {

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry, 'addon', array('name', 'title', 'short'));
	}

	public function getName() {
		return $this->getValue('name');
	}

	public function getTitle() {
		return $this->getValue('title');
	}

	public final function byName($name) {
		return $this->byValue('name', $name);
	}

	public final function byShort($short) {
		return $this->byValue('short', $short);
	}

}

