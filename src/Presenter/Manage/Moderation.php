<?php

namespace Lorry\Presenter\Manage;

use Lorry\Presenter;
use Lorry\ModelFactory;

class Moderation extends Presenter {

	public function get() {
		$this->security->requireModerator();
		$this->offerIdentification();
		$this->security->requireIdentification();

		
		$submitted = array();
		$addons = ModelFactory::build('Addon')->byApprovalSubmitted();
		//print_r($addons);
		foreach($addons as $addon) {
			$submitted[] = array('addon' => array('id' => $addon->getId(), 'title' => $addon->getTitle()));
		}
		$this->context['submitted'] = $submitted;
		
		$this->display('manage/moderation.twig');
	}

}
