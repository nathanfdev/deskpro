<?php

namespace Application\InstallBundle\Data\DefaultData;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\BundleFileHandlingStrategyZip;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationManagerService;

/**
 * Class Apps2Data.
 */
class Apps2Data extends AbstractDefaultData
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function runInstall()
    {
        $assetDir = $this->getContainer()->get('deskpro.app_env')->getAppWwwAssetDir();
        $appsDir  = new \DirectoryIterator($assetDir.'/apps/v2');

        foreach ($appsDir as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                $this->runInstallApp($fileInfo->getPathname());
            }
        }
    }

    /**
     * @param $bundlePath
     *
     * @throws \Exception
     */
    private function runInstallApp($bundlePath)
    {
        $this->getLogger()->info(sprintf('Installing v2 app from path: %s', $bundlePath));

        /** @var BundleFileHandlingStrategyZip $bundleReader */
        $bundleReader = $this->getContainer()->get(BundleFileHandlingStrategyZip::class);

        $bundle = $bundleReader->reader($bundlePath);
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
