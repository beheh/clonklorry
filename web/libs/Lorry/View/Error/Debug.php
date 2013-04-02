<?php

class Lorry_View_Error_Debug extends Lorry_View_Error {

	protected $title;
	protected $details;

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		$this->title = gettext('Internal error.');
		$this->message = '<p>'.gettext('No debug information available.').'</p>';
	}

	protected function render() {
		return $this->lorry->twig->render('error.twig', array('title' => ucfirst($this->title), 'details' => $this->details, 'message' => $this->message));
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setDetails($details) {
		$this->details = $details;
	}

}