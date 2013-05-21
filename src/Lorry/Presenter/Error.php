<?php

namespace Lorry\Presenter;

use Lorry\Presenter;
use Exception;

class Error extends Presenter {

	protected function getCode() {
		return 500;
	}

	protected function getMessage() {
		return 'Internal Server Error';
	}

	protected function getLocalizedMessage() {
		return gettext('Internal server error');
	}

	protected function getLocalizedDescription() {
		return gettext('The server encountered an internal error.');
	}

	public function get($exception = false) {
		header('HTTP/1.1 '.$this->getCode().' '.$this->getMessage());

		$this->context['title'] = $this->getLocalizedMessage();
		$this->context['description'] = $this->getLocalizedDescription();

		$this->display('generic/hero.twig');
	}

}