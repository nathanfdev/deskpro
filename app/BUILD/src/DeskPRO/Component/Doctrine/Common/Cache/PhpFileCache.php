<?php

namespace DeskPRO\Component\Doctrine\Common\Cache;

use Doctrine\Common\Cache\PhpFileCache as BasePhpFileCache;

class PhpFileCache extends BasePhpFileCache
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
