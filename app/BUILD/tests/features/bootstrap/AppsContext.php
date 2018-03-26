<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppZipBundleBuilder;
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
        $appArchive      = AppZipBundleBuilder::fromTmp($tmpRoot)->addFolder($dir)->build();
        $lastPackagedApp = $appArchive->getFilePath();
        DataContext::setPlaceholder('lastPackagedApp', $lastPackagedApp);
    }
}
