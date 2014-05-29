<?php

namespace Lorry\Presenter\Publish\Api;

use Lorry\ApiPresenter;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\FileNotFoundException;

class RemoveFile extends ApiPresenter {

	public function post($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$this->security->requireValidState();

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$filename = QueryFile::sanitizeFilename(filter_input(INPUT_POST, 'fileName'));

		$file = QueryFile::getDataDirectory($this->config, $addon, $release).'/'.$filename;

		if(!file_exists($file)) {
			throw new FileNotFoundException;
		}

		unlink($file);

		$this->display(array('file' => 'removed'));
	}

}
