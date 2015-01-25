<?php

namespace Lorry\Model;

use Lorry\Model;

class Dependency extends Model {

	public function __construct() {
		parent::__construct('dependency', array(
			'release' => 'int',
			'required' => 'int'));
	}

	public function setRelease($release) {
		return $this->setValue('release', $release);
	}

	public function byRelease($release) {
		return $this->byValue('release', $release);
	}

	public function getRelease() {
		return $this->getValue('release');
	}

	/**
	 * @return Release
	 */
	public function fetchRelease() {
		return $this->fetch('Release', 'release');
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

	/**
	 * @return Release
	 */
	public function fetchRequired() {
		return $this->fetch('Release', 'required');
	}

}
