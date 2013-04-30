<?php

namespace Lorry\Views;

use Lorry\View;
use Lorry\Environment;

class Error extends View {

	protected $title;
	protected $message;

	public function __construct(Environment $lorry) {
		parent::__construct($lorry);
		$this->message = gettext('Unknown error.');
	}

	protected function allow() {
		return true;
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

}