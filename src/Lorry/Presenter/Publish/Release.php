<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use DateTime;

class Release extends Presenter {

	public function get($game, $addon, $version) {
		$this->security->requireLogin();
		$user = $this->session->getUser();

		$datetime = new DateTime('tomorrow 12:00');
		$this->context['datetime'] =  $datetime->format('Y-m-d\TH:i:s');
		$datetime = new DateTime();
		$this->context['current_datetime'] = $datetime->format('Y-m-d\TH:i:s');
		$this->display('publish/release.twig');
	}

}