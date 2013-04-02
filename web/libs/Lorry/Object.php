<?php

class Lorry_Object {

	/**
	 *
	 * @var Lorry_Environment
	 */
	protected $lorry;

	public function __construct(Lorry_Environment $lorry) {
		$this->lorry = $lorry;
	}

	public function getLorry() {
		return $this->lorry;
	}

}

