<?php

namespace Lorry\Model;

use Lorry\Model;
use Lorry\ModelFactory;

class Dependency extends Model {

	public function __construct() {
		parent::__construct('dependency', array(
			'addon' => 'int',
			'required' => 'int'));
	}

	public function setAddon($addon) {
		return $this->setValue('addon', $addon);
	}

	public function byAddon($addon) {
		return $this->byValue('addon', $addon);
	}

	public function getAddon() {
		return $this->getValue('addon');
	}

	public function fetchAddon() {
		return ModelFactory::build('Addon')->byId($this->getAddon());
	}

	public function setRequired($required) {
		return $this->setValue('required', $required);
	}

	public function byRequired($required) {
		return $this->byValue('required', $required);
	}

	public function getRequired() {
		return $this->getValue('required');
	}

	public function fetchRequired() {
		return ModelFactory::build('Addon')->byId($this->getRequired());
	}

}