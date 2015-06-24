<?php

namespace Lorry\Service;

use Lorry\Environment;
use Doctrine\Common\Persistence\ObjectManager;
use Lorry\Model\Release;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Lorry\Exception\FileNotFoundException;

class FileService extends AbstractService
{

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    public function __construct(LoggerFactoryInterface $loggerFactory, ObjectManager $manager)
    {
        parent::__construct($loggerFactory);
        $this->manager = $manager;
    }

    protected function sanitizePath($path)
    {
        $path = trim($path, '/');
        $path = '/'.$path.'/';
        preg_replace('|[^a-zA-Z0-9/]|', '', $path);
        $path = trim($path, '/');
        return $path;
    }

    protected function sanitizeFilename($filename)
    {
        $filename = basename($filename);
        return $filename;
    }

    public function ensureWriteable($directory)
    {
        if (!is_writeable($directory)) {
            mkdir($directory);
        }
    }

    public function getLocalFilesystem($subdirectory)
    {
        $subdirectory = $this->sanitizePath($subdirectory);

        $root = Environment::PROJECT_ROOT.'/upload';
        $this->ensureWriteable($root);
        $targetDirectory = $root.'/'.$subdirectory;
        $this->ensureWriteable($targetDirectory);

        return new Filesystem(new LocalAdapter($targetDirectory));
    }

    public function getFilesystemHandler(Release $release)
    {
        return $this->getLocalFilesystem('release'.$release->getId());
    }

    protected $local = null;

    public function queryFile($prefix)
    {
        $filesystem = $this->getLocalFilesystem($prefix);

        $identifier = filter_input(INPUT_GET, 'resumableIdentifier');
        $filename = $this->sanitizeFilename(filter_input(INPUT_GET, 'resumableFilename'));

        if($filesystem->has($filename)) {
            return true;
        }
        else {
            throw new FileNotFoundException;
        }
    }

    public function putFile($prefix)
    {
        $filesystem = $this->getLocalFilesystem($prefix);
    }

}
