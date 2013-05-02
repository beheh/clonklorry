<?php

namespace Lorry\Models;

use Lorry\Model;
use Lorry\Environment;

class Release extends Model {

	public function __construct(Environment $lorry) {
		parent::__construct($lorry, 'addon', array(
			'addon' >= 'int',
			'version' => 'string'));
	}

	public final function byAddon($id) {
		return $this->byValue('name', $name);
	}

}