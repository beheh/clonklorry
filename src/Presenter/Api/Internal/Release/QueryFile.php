<?php

namespace Lorry\Presenter\Api\Internal\Release;

use RuntimeException;
use Lorry\Presenter\Api\Presenter;
use Lorry\Exception\LorryException;
use Lorry\Exception\ForbiddenException;
use Lorry\Model\Addon;
use Lorry\Model\Release;
use Lorry\Environment;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;

class QueryFile extends Presenter
{

    public static function sanitizePath($supplied)
    {
        $file_name = basename($supplied);
        if (!preg_match('/^[-0-9A-Z_\. ]*$/i', $file_name)) {
            throw new Exception(gettext('invalid path'));
        }
        if (strlen($file_name) > 50) {
            throw new Exception(gettext('filename too long'));
        }
        if (strlen($file_name) < 5) {
            throw new Exception(gettext('filename too short'));
        }

        return $file_name;
    }

    public static function sanitizeFilename($supplied)
    {
        $file_name = QueryFile::sanitizePath($supplied);
        if (preg_match('/^.*\.((zip)|(rar)|(tar)|(7z))$/i', $file_name)) {
            throw new Exception(gettext('please upload the individual files instead of an archive'));
        }
        if (!preg_match('/^.*\.((c4d)|(c4s)|(c4f)|(c4g)|(ocd)|(ocs)|(ocf)|(ocg))$/i',
                $file_name)) {
            throw new Exception(gettext('file must end with a clonk extension'));
        }
        return $file_name;
    }

    public static function getFilePath(Addon $addon, Release $release)
    {
        return 'addon'.$addon->getId().'/release'.$release->getId();
    }

    public function get($id, $version)
    {
        if (!$this->session->authenticated()) {
            throw new ForbiddenException();
        }

        $user = $this->session->getUser();
        $addon = \Lorry\Presenter\Publish\Edit::getAddon($this->persistence,
                $id, $user);
        $release = \Lorry\Presenter\Publish\Release::getRelease($this->persistence,
                $addon->getId(), $version);

        $files = array();


        $root = Environment::PROJECT_ROOT.'/upload';
        if (!is_dir($root)) {
            // attempt to create directory
            if (!mkdir($root) && !is_dir($root)) {
                throw new RuntimeException('creation of '.$root.' failed');
            }
        }

        $filesystem = new Filesystem(new LocalAdapter($root));

        $directory = self::getFilePath($addon, $release);

        $files = array();
        if ($filesystem->has($directory)) {
            foreach ($filesystem->listContents(self::getFilePath($addon,
                    $release)) as $file) {
                $entry = array('uniqueIdentifier' => md5($file['basename']));
                if (isset($file['type']) && $file['type'] === 'file') {
                    $entry['fileName'] = $file['basename'];
                    $entry['complete'] = true;
                    $entry['progress'] = 100;
                } else {
                    $entry['fileName'] = strstr($file['basename'], '.parts',
                        true);
                    $entry['complete'] = false;
                    $entry['progress'] = -1;
                }
                $files[] = $entry;
            }
        }
        $this->display(array('files' => $files));
    }
}
