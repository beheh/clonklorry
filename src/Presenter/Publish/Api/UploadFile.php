<?php

namespace Lorry\Presenter\Publish\Api;

use Lorry\ApiPresenter;
use Lorry\Exception;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Model\Addon;
use Lorry\Model\Release;
use Lorry\Model\User;
use Analog;

class UploadFile extends ApiPresenter {

	public static function removeChunkDirectory($chunk_directory) {
		if(!is_dir($chunk_directory)) {
			return false;
		}

		if(rename($chunk_directory, $chunk_directory.'.tmp')) {
			$chunk_directory .= '.tmp';
		}

		foreach(scandir($chunk_directory) as $file) {
			$chunk_file = $chunk_directory.'/'.$file;
			if(is_file($chunk_file)) {
				unlink($chunk_file);
			}
		}

		return rmdir($chunk_directory);
	}

	private function attemptAssembleFile(User $user, Addon $addon, Release $release, $chunk_directory, $file_name, $chunk_size, $total_size) {

		// count all the parts of this file
		$total_files = 0;
		foreach(scandir($chunk_directory) as $file) {
			if(stripos($file, $file_name) !== false) {
				$total_files++;
			}
		}

		// check that all the parts are present
		// the size of the last part is between chunkSize and 2*$chunkSize
		if($total_files * $chunk_size >= ($total_size - $chunk_size + 1)) {

			// create the final destination file
			if(($fp = fopen(QueryFile::getDataDirectory($this->config, $addon, $release).'/'.$file_name, 'w')) !== false) {
				for($i = 1; $i <= $total_files; $i++) {
					fwrite($fp, file_get_contents($chunk_directory.'/'.$file_name.'.part'.$i));
				}
				fclose($fp);
				Analog::info('uploaded file "'.$file_name.'" for "'.$user->getUsername().'"');
			} else {
				return false;
			}

			UploadFile::removeChunkDirectory($chunk_directory);
		}

		return true;
	}

	private function ensureAuthorization() {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}
	}

	public function get($id, $version) {
		$this->security->requireLogin();

		$type = QueryFile::getType();

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$identifier = QueryFile::sanitizePath(filter_input(INPUT_GET, 'resumableIdentifier'));
		$file_name = QueryFile::sanitizeFilename(filter_input(INPUT_GET, 'resumableFilename'));

		if(!$identifier || !$file_name) {
			throw new FileNotFoundException;
		}

		switch($type) {
			case 'data':
				$target_directory = QueryFile::getDataDirectory($this->config, $addon, $release);
				break;
			case 'asset':
				$target_directory = QueryFile::getAssetDirectory($this->config, $addon, $release);
				break;
		}

		$chunk_directory = $target_directory.'/'.$identifier.'.parts';
		$part_file = $chunk_directory.'/'.$file_name.'.part'.filter_input(INPUT_GET, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);

		if(!file_exists($part_file)) {
			throw new FileNotFoundException;
		}
		$this->display(array('chunk' => 'exists'));
	}

	public function post($id, $version) {
		$this->security->requireLogin();
		$this->security->requireValidState();
		$this->security->requireUploadRights();

		$type = QueryFile::getType();

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$file_name = QueryFile::sanitizeFilename(filter_input(INPUT_POST, 'resumableFilename'));

		$file = $_FILES['file'];

		if($file['error'] != 0) {
			throw new Exception(gettext('error receiving file chunk'));
		}

		switch($type) {
			case 'data':
				$target_directory = QueryFile::getDataDirectory($this->config, $addon, $release);
				break;
			case 'asset':
				$target_directory = QueryFile::getAssetDirectory($this->config, $addon, $release);
				break;
		}

		if(!is_dir($target_directory)) {
			mkdir($target_directory, 0777, true);
		}

		if(is_file($target_directory.'/'.$file_name)) {
			throw new Exception(gettext('file already exists'));
		}

		$identifier = QueryFile::sanitizePath(filter_input(INPUT_POST, 'resumableIdentifier'));

		$chunk_directory = $target_directory.'/'.$identifier.'.parts';
		if(!is_dir($chunk_directory)) {
			mkdir($chunk_directory);
		}

		// final check
		if(!is_dir($chunk_directory)) {
			throw new Exception(gettext('could not create upload directory'));
		}

		$chunk_number = filter_input(INPUT_POST, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);
		$chunk_size = filter_input(INPUT_POST, 'resumableChunkSize', FILTER_SANITIZE_NUMBER_INT);
		$total_size = filter_input(INPUT_POST, 'resumableTotalSize', FILTER_SANITIZE_NUMBER_INT);

		if($total_size > $this->config->getSize('upload/datasize')) {
			throw new Exception(gettext('file too big'));
		}
		if($chunk_number > 1000) {
			throw new Exception(gettext('file chunk count too big'));
		}
		if($chunk_size < 1 || $total_size < 1) {
			throw new Exception(gettext('invalid file chunk size'));
		}
		if(($chunk_number * $chunk_size) > ($total_size + $chunk_size)) {
			throw new Exception(gettext('file chunk exceeds total size'));
		}

		$part_file = $chunk_directory.'/'.$file_name.'.part'.$chunk_number;

		if(!move_uploaded_file($file['tmp_name'], $part_file)) {
			throw new Exception(gettext('error saving file chunk'));
		}


		if(!$this->attemptAssembleFile($user, $addon, $release, $chunk_directory, $file_name, $chunk_size, $total_size)) {
			throw new Exception(gettext('error assembling file'));
		}

		$this->display(array('chunk' => 'received'));
	}

}
