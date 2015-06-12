<?php

namespace Lorry\Service;

use Lorry\Environment;
use Lorry\Model\Addon;
use Lorry\Model\Release;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;

class FileService extends AbstractService
{

    public function getBaseUpload(Addon $addon, Release $release)
    {
    }

    public function getFilesystemHandler(Addon $addon, Release $release)
    {
        $root = Environment::PROJECT_ROOT.'/addon'.$addon.'/release'.$release;
        if (!is_writeable($root)) {
            mkdir($root);
        }

        $filesystem = new Filesystem(new LocalAdapter($root));

        return $filesystem;
    }
}
