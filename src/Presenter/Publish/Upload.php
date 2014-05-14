<?php

namespace Lorry\Presenter\Publish;

use Lorry\Presenter;
use Lorry\ModelFactory;
use Lorry\Exception;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\FileNotFoundException;
use Lorry\Exception\OutputCompleteException;
use Lorry\Model\User;
use Lorry\Model\Addon;
use Lorry\Model\Release;

class Upload extends Presenter {

	private function getAddon($id) {
		$addon = ModelFactory::build('Addon')->byId($id);
		if(!$addon) {
			throw new FileNotFoundException();
		}
		if($addon->getOwner() != $this->session->getUser()->getId()) {
			throw new ForbiddenException();
		}
		return $addon;
	}

	private function getRelease($id, $version) {
		$release = ModelFactory::build('Release')->byVersion($version, $id);
		if(!$release) {
			throw new FileNotFoundException();
		}
		return $release;
	}

	const UPLOAD_DIR = '../app/upload/publish';

	private function getTargetDirectory(User $user, Addon $addon, Release $release) {
		return self::UPLOAD_DIR.'/user'.$user->getId().'/addon'.$addon->getId().'/release'.$release->getId();
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
			if(($fp = fopen($this->getTargetDirectory($user, $addon, $release).'/'.$file_name, 'w')) !== false) {
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

	public function get($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$user = $this->session->getUser();
		$addon = $this->getAddon($id);
		$release = $this->getRelease($addon->getId(), $version);

		$chunk_directory = $this->getTargetDirectory($user, $addon, $release).'/'.basename(filter_input(INPUT_GET, 'resumableIdentifier'));
		$part_file = $chunk_directory.'/'.filter_input(INPUT_GET, 'resumableFilename').'.part'.filter_input(INPUT_GET, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);

		if(!file_exists($part_file)) {
			throw new FileNotFoundException();
		} else {
			throw new OutputCompleteException();
		}
	}

	public function post($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$user = $this->session->getUser();
		$addon = $this->getAddon($id);
		$release = $this->getRelease($addon->getId(), $version);

		$file = $_FILES['file'];

		if($file['error'] != 0) {
			throw new Exception('Error receiving file chunk.');
		}

		$target_directory = $this->getTargetDirectory($user, $addon, $release);
		if(!is_dir($target_directory)) {
			mkdir($target_directory, 0777, true);
		}
		
		$chunk_directory = $target_directory.'/'.basename(filter_input(INPUT_POST, 'resumableIdentifier'));
		if(!is_dir($chunk_directory)) {
			mkdir($chunk_directory);
		}

		$file_name = basename(filter_input(INPUT_POST, 'resumableFilename'));

		$part_file = $chunk_directory.'/'.$file_name.'.part'.filter_input(INPUT_POST, 'resumableChunkNumber', FILTER_SANITIZE_NUMBER_INT);

		if(!move_uploaded_file($file['tmp_name'], $part_file)) {
			throw new Exception('Error saving file chunk.');
		}

		if(!$this->attemptAssembleFile($user, $addon, $release, $chunk_directory, $file_name, filter_input(INPUT_POST, 'resumableChunkSize', FILTER_SANITIZE_NUMBER_INT), filter_input(INPUT_POST, 'resumableTotalSize', FILTER_SANITIZE_NUMBER_INT))) {
			throw new Exception('Error assembling file.');
		}
	}

}
