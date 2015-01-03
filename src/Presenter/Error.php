<?php

namespace Lorry\Presenter;

use Analog;
use Lorry\Presenter;
use \Exception;

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
		return gettext('The server encountered an internal error processing the request.');
	}

	public function get(Exception $exception = null) {
		header('HTTP/1.1 '.$this->getCode().' '.$this->getMessage());

		$this->context['title'] = $this->getLocalizedMessage();
		$this->context['description'] = $this->getLocalizedDescription();

		if($exception) {
			if(get_class($this) == __CLASS__) {
				$message = get_class($exception).': '.$exception->getMessage().' in '.$exception->getTraceAsString();
				Analog::error($message);
			}
			else {
				Analog::debug(get_class($exception).': '.$exception->getMessage());
			}
			if($this->config->get('debug')) {
				$this->context['raw'] = '<pre>'.get_class($exception).': '.$exception->getMessage().'<br><br>'.$exception->getTraceAsString().'</pre>';
			}
		}

		$this->context['hide_greeter'] = true;

		$this->display('generic/hero.twig');
	}

}
