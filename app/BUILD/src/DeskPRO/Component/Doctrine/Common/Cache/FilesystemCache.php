<?php

namespace DeskPRO\Component\Doctrine\Common\Cache;

use Doctrine\Common\Cache\FilesystemCache as BaseFilesystemCache;
use DpRun\DpFsProxyStreamWrapper;

class FilesystemCache extends BaseFilesystemCache
{
    /**
     * {@inheritdoc}
     */
    public function __construct($directory, $extension = self::EXTENSION)
    {
        if (!defined('DPC_IS_READ_ONLY_FS')) {
            return parent::__construct($directory, $extension, umask());
        }

        $ref = (new \ReflectionObject($this))
            ->getParentClass()
            ->getParentClass()
        ;

        $umask = umask();

        if ( ! is_int($umask)) {
            throw new \InvalidArgumentException(sprintf(
                'The umask parameter is required to be integer, was: %s',
                gettype($umask)
            ));
        }

        $this->setFileCacheProp($ref, 'umask', $umask);

        if ( ! $this->createPathIfNeeded($directory, $umask)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" does not exist and could not be created.',
                $directory
            ));
        }

        if ( ! is_writable($directory)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $directory
            ));
        }

        $this->directory = DpFsProxyStreamWrapper::realpath($directory);

        $this->setFileCacheProp($ref, 'extension', (string) $extension);
        $this->setFileCacheProp($ref, 'directoryStringLength', strlen($this->directory));
        $this->setFileCacheProp($ref, 'extensionStringLength', strlen((string) $extension));
        $this->setFileCacheProp($ref, 'isRunningOnWindows', defined('PHP_WINDOWS_VERSION_BUILD'));
    }

    /**
     * @see \Doctrine\Common\Cache\FileCache::createPathIfNeeded()
     */
    private function createPathIfNeeded($path, $umask)
    {
        if ( ! is_dir($path)) {
            if (false === @mkdir($path, 0777 & (~$umask), true) && !is_dir($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilename($id)
    {
        return FileCacheUtil::getFilename($id, $this->getDirectory(), $this->getExtension());
    }

    /**
     * Sets the private properties of @throws \Exception
     * @see \Doctrine\Common\Cache\FileCache
     */
    protected function setFileCacheProp(\ReflectionClass $ref, $propName, $value)
    {
        $prop = $ref->getProperty($propName);

        if (!$prop->isPrivate()) {
            throw new \Exception(sprintf(
                'Cannot modify property "%s" on "%s" as scope must be private',
                $propName,
                $ref->getName()
            ));
        }

        $prop->setAccessible(true);
        $prop->setValue($this, $value);
        $prop->setAccessible(false);
    }
}
