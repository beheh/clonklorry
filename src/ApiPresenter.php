<?php

namespace Lorry;

use Lorry\Presenter;
use Lorry\Exception;

class ApiPresenter extends Presenter {

	public function handle($method, $parameters) {
		header('Content-Type: text/json');
		try {
			return call_user_func_array(array($this, $method), $parameters);
		} catch(Exception $ex) {
			$error = array('error' => $ex->getApiType());
			$this->display($error);
		}
		return false;
	}

	public function display($result) {
		echo json_encode($result);
	}

}
