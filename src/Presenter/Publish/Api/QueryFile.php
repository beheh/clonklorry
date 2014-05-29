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

	public static function sanitizeFilename($supplied) {
		$file_name = basename($supplied);
		if(!preg_match('/^[-0-9A-Z_\.]/i', $file_name)) {
			throw new Exception(gettext('invalid filename'));
		}
		return $file_name;
	}

	public static function getType() {
		$type = filter_input(INPUT_GET, 'type');
		if(!in_array($type, array('asset', 'data'))) {
			throw new Exception('unknown type');
		}
		if($type == 'asset') {
			throw new Lorry\Exception\NotImplementedException;
		}
		return $type;
	}

	public static function getAssetDirectory(ConfigService $config, Addon $addon, Release $release) {
		$base = $config->get('upload/assets');
		if(!is_dir($base) || !is_writable($base)) {
			throw new \Exception('asset directory does not exist or is not writeable');
		}
		return $base.'/'.$release->getAssetSecret();
	}

	public static function getDataDirectory(ConfigService $config, Addon $addon, Release $release) {
		$base = $config->get('upload/data');
		if(!is_dir($base) || !is_writable($base)) {
			throw new \Exception('data directory does not exist or is not writeable');
		}
		return $base.'/addon'.$addon->getId().'/release'.$release->getId();
	}

	public function get($id, $version) {
		if(!$this->session->authenticated()) {
			throw new ForbiddenException();
		}

		$user = $this->session->getUser();
		$addon = \Lorry\Presenter\Publish\Edit::getAddon($id, $user);
		$release = \Lorry\Presenter\Publish\Release::getRelease($addon->getId(), $version);

		$finder = new Finder();
		$found = $finder->files()->depth('== 0')->in(QueryFile::getDataDirectory($this->config, $addon, $release));

		$files = array();
		foreach($found as $file) {
			$files[] = array('uniqueIdentifier' => count($files), 'fileName' => $file->getRelativePathname());
		}

		$this->display(array('files' => $files));
	}

}
