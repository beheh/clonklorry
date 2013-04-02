<?php

class Lorry_View_Error_Notfound extends Lorry_View_Error {

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		header('HTTP/1.1 404 Not Found');
		$this->setMessage(gettext('file not found'));
	}

}