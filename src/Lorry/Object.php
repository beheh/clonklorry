<?php

namespace Lorry;

class Object {

	/**
	 *
	 * @var \Lorry\Environment
	 */
	protected $lorry;

	public function __construct(Environment $lorry) {
		$this->lorry = $lorry;
	}

	public function getLorry() {
		return $this->lorry;
	}

}