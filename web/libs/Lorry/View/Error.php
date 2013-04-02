<?php

class Lorry_View_Error extends Lorry_View {

	protected $title;
	protected $message;

	public function __construct(\Lorry_Environment $lorry) {
		parent::__construct($lorry);
		$this->message = gettext('Unknown error.');
	}

	protected function render() {
		return $this->lorry->twig->render('error.twig', array('message' => ucfirst($this->message), 'title' => $this->title));
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	protected function allow() {
		return true;
	}

}

