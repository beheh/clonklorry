<?php

namespace Lorry\Presenter\Publish\Api;

use Lorry\ApiPresenter;
use Lorry\Exception;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\OutputCompleteException;
use Lorry\Model\Addon;
use Lorry\Model\Release;
use Lorry\Model\User;
use Analog;

class UploadFile extends ApiPresenter {

	private function getType() {
		$type = filter_input(INPUT_GET, 'type');
		if(!in_array($type, array('asset', 'data'))) {
			throw new Exception('unknown type');
		}
		if($type == 'asset') {
			throw new Lorry\Exception\NotImplementedException;
		}
		return $type;
	}

	private function getAssetDirectory(Addon $addon, Release $release) {
		$base = $this->config->get('upload/assets');
		if(!is_dir($base) || !is_writable($base)) {
			throw new \Exception('asset directory does not exist or is not writeable');
		}
		return $base.'/'.$release->getAssetSecret();
	}

	private function getDataDirectory(Addon $addon, Release $release) {
		$base = $this->config->get('upload/data');
		if(!is_dir($base) || !is_writable($base)) {
			throw new \Exception('data directory does not exist or is not writeable');
		}
		return $base.'/addon'.$addon->getId().'/release'.$release->getId();
	}

	private function sanitizeFilename($supplied) {
		$file_name = basename($supplied);
		if(!preg_match('/^[-0-9A-Z_\.]/i', $file_name)) {
			throw new Exception(gettext('invalid filename'));
		}
		return $file_name;
	}

	private function removeChunkDirectory($chunk_directory) {
		if(rename($chunk_directory, $chunk_directory.'.tmp')) {
			$chunk_directory .= '.tmp';
		}

		foreach(scandir($chunk_directory) as $file) {
			$chunk_file = $chunk_directory.'/'.$file;
			if(is_file($chunk_file)) {
				unlink($chunk_file);
			}
		}

		rmdir($chunk_directory);
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
			if(($fp = fopen($this->getDataDirectory($addon, $release).'/'.$file_name, 'w')) !== false) {
				for($i = 1; $i <= $total_files; $i++) {
					fwrite($fp, file_get_contents($chunk_directory.'/'.$file_name.'.part'.$i));
				}
				fclose($fp);
				Analog::info('uploaded file "'.$file_name.'" for "'.$user->getUsername().'"');
			} else {
				return false;
			}

			$this->removeChunkDirectory($chunk_directory);
		}

		return true;
	}

	public function get($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$type = $this->getType();

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$file_name = $this->sanitizeFilename(filter_input(INPUT_GET, 'resumableFilename'));

		$chunk_directory = $this->getDataDirectory($addon, $release).'/'.$file_name;
		$part_file = $chunk_directory.'/'.$file_name.'.part'.filter_input(INPUT_GET, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);

		if(!file_exists($part_file)) {
			throw new FileNotFoundException();
		} else {
			throw new OutputCompleteException();
		}

		$this->display(array('chunk' => 'exists'));
	}

	public function post($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$this->security->requireValidState();

		$type = $this->getType();

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$file = $_FILES['file'];

		if($file['error'] != 0) {
			throw new Exception(gettext('error receiving file chunk'));
		}

		switch($type) {
			case 'data':
				$target_directory = $this->getDataDirectory($addon, $release);
				break;
			case 'asset':
				$target_directory = $this->getAssetDirectory($addon, $release);
				break;
		}

		if(!is_dir($target_directory)) {
			mkdir($target_directory, 0777, true);
		}

		if(is_file($target_directory.'/'.$file_name)) {
			throw new Exception(gettext('file already exists'));
		}

		$identifier = $this->sanitizeFilename(filter_input(INPUT_POST, 'resumableIdentifier'));

		$chunk_directory = $target_directory.'/'.$identifier;
		if(!is_dir($chunk_directory)) {
			mkdir($chunk_directory);
		}

		$file_name = $this->sanitizeFilename(filter_input(INPUT_POST, 'resumableFilename'));

		// final check
		if(!is_dir($chunk_directory)) {
			throw new Exception(gettext('could not create upload directory'));
		}

		$part_file = $chunk_directory.'/'.$file_name.'.part'.filter_input(INPUT_POST, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);

		if(!move_uploaded_file($file['tmp_name'], $part_file)) {
			throw new Exception(gettext('error saving file chunk'));
		}

		if(!$this->attemptAssembleFile($user, $addon, $release, $chunk_directory, $file_name, filter_input(INPUT_POST, 'resumableChunkSize', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'resumableTotalSize', FILTER_SANITIZE_NUMBER_INT))) {
			throw new Exception(gettext('error assembling file'));
		}

		$this->display(array('result' => 'complete'));
	}

}
