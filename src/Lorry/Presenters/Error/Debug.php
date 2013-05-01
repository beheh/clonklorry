<?php

namespace Lorry\Presenters\Error;

use Lorry\Environment;
use Lorry\Presenters\Error;

class Debug extends Error {

	protected $title;
	protected $details;

	public function __construct(Environment $lorry) {
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