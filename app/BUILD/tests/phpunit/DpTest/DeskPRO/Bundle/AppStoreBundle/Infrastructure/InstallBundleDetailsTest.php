<?php

namespace DpTest\DeskPRO\Bundle\AppStoreBundle\Infrastructure;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\InstallBundleDetails;
use DpTest\DeskProTestCase;

class InstallBundleDetailsTest extends DeskProTestCase
{
    public function testReloadRequiredReturnsTrue()
    {
        $details = new InstallBundleDetails(
            $this->getMockBuilder(App::class)->getMock(),
            InstallBundleDetails::INSTALL_TYPE_UPGRADE,
            false
        );
        $this->assertTrue(
            $details->reloadRequired(),
            "reload should be required when the package is upgraded"
        );

        $details = new InstallBundleDetails(
            $this->getMockBuilder(App::class)->getMock(),
            InstallBundleDetails::INSTALL_TYPE_INSTALL,
            true
        );
        $this->assertTrue(
            $details->reloadRequired(),
            "reload should be required when forceConfiguration is true"
        );

    }
}
