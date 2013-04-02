<?php

class Lorry_View_Error_Internal extends Lorry_View_Error {

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		$this->message = gettext('Internal error.');
	}

}