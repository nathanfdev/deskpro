<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;

class ManifestSchemaLocator
{
    /** @var FileLocator */
    private $fileLocator;

    /** @var string */
    private $schemaDir;

    /** @var string */
    private $currentVersion;

    /**
     * ManifestSchemaLocator constructor.
     *
     * @param FileLocator $locator
     * @param string $schemaDir
     * @param string $currentVersion
     */
    public function __construct(FileLocator $locator, $schemaDir, $currentVersion)
    {
        $this->fileLocator = $locator;
        $this->schemaDir = $schemaDir;
        $this->currentVersion = $currentVersion;
    }

    /**
     * @param $version
     * @return null|\SplFileInfo
     */
    public function locate($version)
    {
        $schemaVersion =  $version === $this->currentVersion ? 'current' : $version;
        $schemaName = sprintf('schema.%s.json', $schemaVersion);
        return $this->locateSchemaFile($schemaName);
    }

    public function locateCurrent()
    {
        $schemaName = sprintf('schema.%s.json', 'current');
        return $this->locateSchemaFile($schemaName);
    }

    private function locateSchemaFile($name)
    {
        $schemaPath = $this->fileLocator->locate($this->schemaDir . '/' . $name);
        $schemaInfo = new \SplFileInfo($schemaPath);
        if ($schemaInfo->isFile() && $schemaInfo->isReadable()) {
            return $schemaInfo;
        }
        return null;
    }
}


