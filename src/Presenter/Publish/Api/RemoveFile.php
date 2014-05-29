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

		$filename = QueryFile::sanitizePath(filter_input(INPUT_POST, 'fileName'));
		$unfiltered_identifier = filter_input(INPUT_POST, 'uniqueIdentifier');
		$identifier = false;
		if($unfiltered_identifier) {
			$identifier = QueryFile::sanitizePath($unfiltered_identifier);
		}

		$target_directory = QueryFile::getDataDirectory($this->config, $addon, $release);
		$chunk_directory = $target_directory.'/'.$identifier.'.parts';

		$file = $target_directory.'/'.$filename;

		if(!file_exists($file) && (!$identifier || !is_dir($chunk_directory))) {
			throw new FileNotFoundException;
		}

		if(!unlink($file) && !UploadFile::removeChunkDirectory($chunk_directory)) {
			throw new \Exception('file removal failed');
		}

		$this->display(array('file' => 'removed'));
	}

}
