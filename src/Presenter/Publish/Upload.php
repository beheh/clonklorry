<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\Exception;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\FileNotFoundException;
use Lorry\Model\User;

class Upload extends Presenter {

	const UPLOAD_DIR = '../app/upload/lorry';

	private function ensureUserDirectory(User $user) {
		$user_directory = self::UPLOAD_DIR.'/user'.$user->getId();
		if(!is_dir($user_directory)) {
			mkdir($user_directory);
		}
		return true;
	}

	private function getUserDirectory(User $user) {
		$this->ensureUserDirectory($user);
		return self::UPLOAD_DIR.'/user'.$user->getId();
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

	private function attemptAssembleFile($chunk_directory, $file_name, $chunk_size, $total_size) {

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
			if(($fp = fopen(self::UPLOAD_DIR.'/'.$file_name, 'w')) !== false) {
				for($i = 1; $i <= $total_files; $i++) {
					fwrite($fp, file_get_contents($chunk_directory.'/'.$file_name.'.part'.$i));
				}
				fclose($fp);
			} else {
				return false;
			}

			$this->removeChunkDirectory($chunk_directory);
		}

		return true;
	}

	public function get($addonid, $release) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$user = $this->session->getUser();
		$chunk_directory = $this->getUserDirectory($user).'/'.basename(filter_input(INPUT_GET, 'resumableIdentifier'));

		$part_file = $chunk_directory.'/'.filter_input(INPUT_GET, 'resumableFilename').'.part'.filter_input(INPUT_GET, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);
		if(!file_exists($part_file)) {
			throw new FileNotFoundException();
		}
	}

	public function post($addonid, $release) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$file = $_FILES['file'];

		if($file['error'] != 0) {
			throw new Exception('Error receiving file chunk.');
		}

		$user = $this->session->getUser();
		$chunk_directory = $this->getUserDirectory($user).'/'.basename(filter_input(INPUT_POST, 'resumableIdentifier'));

		if(!is_dir($chunk_directory)) {
			mkdir($chunk_directory);
		}

		$file_name = basename(filter_input(INPUT_POST, 'resumableFilename'));

		$part_file = $chunk_directory.'/'.$file_name.'.part'.filter_input(INPUT_POST, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);

		if(!move_uploaded_file($file['tmp_name'], $part_file)) {
			throw new Exception('Error saving file chunk.');
		}

		if(!$this->attemptAssembleFile($chunk_directory, $file_name, filter_input(INPUT_POST, 'resumableChunkSize', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'resumableTotalSize', FILTER_SANITIZE_NUMBER_INT))) {
			throw new Exception('Error assembling file.');
		}
	}

}
