<?php

namespace Lorry\Presenter\Publish\Api;

use Lorry\ApiPresenter;
use Lorry\Exception;
use Lorry\Exception\ForbiddenException;
use Lorry\Service\ConfigService;
use Lorry\Model\Addon;
use Lorry\Model\Release;
use Symfony\Component\Finder\Finder;

class QueryFile extends ApiPresenter {

	public static function sanitizePath($supplied) {
		$file_name = basename($supplied);
		if(!preg_match('/^[-0-9A-Z_\.]*$/i', $file_name)) {
			throw new Exception(gettext('invalid path'));
		}
		if(strlen($file_name) > 50) {
			throw new Exception(gettext('filename too long'));
		}
		if(strlen($file_name) < 5) {
			throw new Exception(gettext('filename too short'));
		}

		return $file_name;
	}

	public static function sanitizeFilename($supplied) {
		$file_name = QueryFile::sanitizePath($supplied);
		if(preg_match('/^.*\.((zip)|(rar)|(tar)|(7z))$/i', $file_name)) {
			throw new Exception(gettext('please upload the individual files instead of an archive'));
		}
		if(!preg_match('/^.*\.((c4d)|(c4s)|(c4f)|(c4g)|(ocd)|(ocs)|(ocf)|(ocg))$/i', $file_name)) {
			throw new Exception(gettext('file must end with a clonk extension'));
		}
		return $file_name;
	}

	public static function getType() {
		$type = filter_input(INPUT_GET, 'type');
		if($type == 'asset') {
			throw new Lorry\Exception\NotImplementedException;
		}
		if(in_array($type, array('asset', 'data'))) {
			return $type;
		}
		throw new Exception('unknown type');
	}

	public static function getAssetDirectory(ConfigService $config, Addon $addon, Release $release) {
		$base = $config->get('upload/assets');
		if(is_dir($base) && is_writable($base)) {
			return $base.'/'.$release->getAssetSecret();
		}
		throw new \Exception('asset directory does not exist or is not writeable');
	}

	public static function getDataDirectory(ConfigService $config, Addon $addon, Release $release) {
		$base = $config->get('upload/data');
		if(is_dir($base) && is_writable($base)) {
			return $base.'/addon'.$addon->getId().'/release'.$release->getId();
		}
		throw new \Exception('data directory does not exist or is not writeable');
	}

	public function get($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$files = array();

		$directory = QueryFile::getDataDirectory($this->config, $addon, $release);
		if(is_dir($directory)) {

			$finder = new Finder();
			$found = $finder->files()->depth('== 0')->in($directory);

			$i = 0;
			foreach($found as $file) {
				$files[] = array('uniqueIdentifier' => md5($i), 'fileName' => $file->getRelativePathname(), 'complete' => true, 'progress' => 100);
				$i++;
			}
		}

		$this->display(array('files' => $files));
	}

}
