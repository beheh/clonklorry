<?php

namespace Lorry\Models;

use Lorry\Model;
use Lorry\Environment;

class Addon extends Model {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry, 'addon', array(
			'name' => 'string',
			'title' => 'string',
			'short' => 'string'));
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

