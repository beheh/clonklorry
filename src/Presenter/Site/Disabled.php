<?php

namespace Lorry\Presenter\Site;

use Lorry\Presenter;

class Disabled extends Presenter {

	public function get() {
		// don't localise here
		$notice = $this->config->get('notice/text');
		if($notice) {
			$this->context['title'] = $notice;
		} else {
			$this->context['title'] = $this->config->get('brand').' is currently disabled';
			$this->context['description'] = 'Please come back later.';
		}
		$this->context['nobuttons'] = true;
		$this->display('generic/hero.twig');
	}

}
