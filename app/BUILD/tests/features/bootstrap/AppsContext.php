<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\ZipArchiveBundleWriter;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationManagerService;
use DpBehat\Data\DataContext;

class AppsContext extends BaseContext
{
    /**
     * @Given I package the app from folder :folder
     *
     * @param string $folder
     */
    public function iPackageTheApp($folder)
    {
        /** @var \DpRun\DpEnv $dpEnv */
        $dpEnv   = $GLOBALS['DP_ENV'];
        $tmpRoot = $dpEnv->getUserTmpDir();

        $dir             = $this->getTestDir($folder);
        $appArchive      = ZipArchiveBundleWriter::fromTmp($tmpRoot)->addFolder($dir)->build();
        $lastPackagedApp = $appArchive->getFilePath();
        DataContext::setPlaceholder('lastPackagedApp', $lastPackagedApp);
    }

    /**
     * @Given I install the app from folder :folder
     *
     * @param string $folder
     */
    public function iInstallTheApp($folder)
    {
        /** @var \DpRun\DpEnv $dpEnv */
        $dpEnv   = $GLOBALS['DP_ENV'];
        $tmpRoot = $dpEnv->getUserTmpDir();

        $dir             = $this->getTestDir($folder);
        $appArchive      = ZipArchiveBundleWriter::fromTmp($tmpRoot)->addFolder($dir)->build();

        /** @var ApplicationManagerService $instanceCreator */
        $instanceCreator = $this->get('apps2.application_manager');
        $instance        = $instanceCreator->createFirstInstance($appArchive);

        DataContext::setPlaceholder('lastCreatedInstanceId', $instance->getId());
        DataContext::setPlaceholder('lastInstalledAppId', $instance->getApplicationId());
    }
}
