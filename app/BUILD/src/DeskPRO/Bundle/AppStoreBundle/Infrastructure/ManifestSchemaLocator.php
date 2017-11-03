<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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


