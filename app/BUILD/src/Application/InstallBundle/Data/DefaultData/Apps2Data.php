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
