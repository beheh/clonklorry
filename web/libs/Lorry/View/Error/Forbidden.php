<?php

class Lorry_View_Error_Forbidden extends Lorry_View_Error {

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		header('HTTP/1.1 403 Forbidden');
		if($this->lorry->session->authenticated()) {
			$this->setMessage(gettext('Access denied.'));
		} else {
			$this->setTitle(gettext('Access denied.'));
			$this->setMessage(gettext('You could try logging in.'));
		}
	}

}