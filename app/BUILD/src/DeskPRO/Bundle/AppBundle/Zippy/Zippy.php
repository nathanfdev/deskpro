<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

use Alchemy\Zippy\Adapter\AdapterContainer;
use Alchemy\Zippy\Exception\FormatNotSupportedException;
use Alchemy\Zippy\Exception\NoAdapterOnPlatformException;

class Zippy extends \Alchemy\Zippy\Zippy
{
    /**
     * {@inheritDoc}
     */
    public static function load()
    {
        $adapters = AdapterContainer::load();
        $factory = new Zippy($adapters);

        // Only support zip archives
        $factory->addStrategy(new ZipFileStrategy($adapters));
        $factory->addStrategy(new ZipFileNoExtStrategy($adapters));

        return $factory;
    }

    /**
     * Allow for "no file extension" mapped strategies
     *
     * {@inheritDoc}
     */
    public function getAdapterFor($extension)
    {
        $strategies = $this->getStrategies();

        if (!isset($strategies[$extension])) {
            throw new FormatNotSupportedException(sprintf('No strategy for %s extension', $extension));
        }

        foreach ($strategies[$extension] as $strategy) {
            foreach ($strategy->getAdapters() as $adapter) {
                if ($adapter->isSupported()) {
                    return $adapter;
                }
            }
        }

        throw new NoAdapterOnPlatformException(sprintf('No adapter available for %s on this platform', $extension));
    }

    /**
     * Create an archive from the files/folders in the root of the source directory
     *
     * @param string $archivePath
     * @param string $sourceDir
     * @param bool $isRecursive
     * @return \Alchemy\Zippy\Archive\ArchiveInterface
     */
    public function createFromDir($archivePath, $sourceDir, $isRecursive = true)
    {
        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException(sprintf('%s must be a directory', $sourceDir));
        }

        $files = array_map(function ($d) use ($sourceDir) {
            return "{$sourceDir}/{$d}";
        }, array_filter(scandir($sourceDir), function ($d) {
            return !($d === '.' || $d === '..');
        }));

        return $this->create($archivePath, $files, $isRecursive);
    }
}
