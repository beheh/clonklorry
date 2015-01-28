<?php

namespace Lorry\Presenter\Api\Internal\Release;

use Lorry\Presenter\Api\Presenter;
use Lorry\Exception\ForbiddenException;
use Lorry\Exception\FileNotFoundException;
use Lorry\Environment;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Analog\Analog;
use RuntimeException;

class RemoveFile extends Presenter {

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

		$root = Environment::PROJECT_ROOT.'/upload';

		$filesystem = new Filesystem(new LocalAdapter($root.'/'.QueryFile::getFilePath($addon, $release)));

		if(!$filesystem->has($filename)) {
			if($filesystem->has($filename.'.parts')) {
				$filename .= '.parts';
			}
			else {
				throw new FileNotFoundException;
			}
		}

		$file = $filesystem->get($filename);

		if($file->isDir()) {
			if(!$filesystem->deleteDir($filename)) {
				throw new RuntimeException('removal of directory '.$filename.' failed (addon '.$addon->getId().', release '.$release->getId().')');
			}
		} elseif($file->isFile()) {
			if(!$filesystem->delete($filename)) {
				throw new RuntimeException('removal of file '.$filename.' failed (addon '.$addon->getId().', release '.$release->getId().')');
			}
		} else {
			throw new RuntimeException('unknown filetype at '.$filename);
		}



		//$target_directory = QueryFile::getDataDirectory($this->config, $addon, $release);
		//$chunk_directory = $target_directory.'/'.$identifier.'.parts';
		//$file = $target_directory.'/'.$filename;

		/* if(!file_exists($file) && (!$identifier || !is_dir($chunk_directory))) {
		  throw new FileNotFoundException;
		  }

		  if(!unlink($file) && !UploadFile::removeChunkDirectory($chunk_directory)) {
		  throw new \Exception('file removal failed');
		  } */

		Analog::info('removed file "'.$filename.'" for "'.$user->getUsername().'"');

		$this->display(array('file' => 'removed'));
	}

}
