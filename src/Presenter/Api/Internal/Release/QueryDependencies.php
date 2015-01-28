<?php

namespace Lorry\Presenter\Api\Internal\Release;

use Lorry\Presenter\Api\Presenter;
use Lorry\Exception\ForbiddenException;

class QueryDependencies extends Presenter {

	public function get($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$dependencies = array();

		$this->display(array('dependencies' => $dependencies));
	}

}
