<?php

namespace DeskPRO\Component\Doctrine\Common\Cache;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;

class FilesystemCache extends BaseFilesystemCache
{
    /**
     * {@inheritdoc}
     */
    public function __construct($directory, $extension = self::EXTENSION)
    {
        parent::__construct($directory, $extension, umask());
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilename($id)
    {
        return FileCacheUtil::getFilename($id, $this->getDirectory(), $this->getExtension());
    }
}
