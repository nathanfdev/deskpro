<?php

namespace Application\InstallBundle\Data\DefaultData;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationManagerService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppZipArchiveBundle;

/**
 * Class Apps2Data.
 */
class Apps2Data extends AbstractDefaultData
{
    /**
     * {@inheritdoc}
     */
    public function runInstall()
    {
        $assetDir = $this->getContainer()->get('deskpro.app_env')->getAppWwwAssetDir();
        $appsDir  = new \DirectoryIterator($assetDir.'/apps/v2');

        foreach ($appsDir as $fileInfo) {
            if (!$fileInfo->isDot()) {
                $this->runInstallApp($fileInfo->getPathname());
            }
        }
    }

    private function runInstallApp($bundlePath)
    {
        $this->getLogger()->info(sprintf('Installing v2 app from path: %s', $bundlePath));

        $bundle = AppZipArchiveBundle::fromFile($bundlePath);
        /** @var ApplicationManagerService $appManager */
        $appManager = $this->getContainer()->get('apps2.application_manager');
        $appManager->createOrUpdateAppEntity($bundle);
    }

    /**
     * {@inheritdoc}
     */
    public function runSync()
    {
        $this->runInstall();
    }
}
