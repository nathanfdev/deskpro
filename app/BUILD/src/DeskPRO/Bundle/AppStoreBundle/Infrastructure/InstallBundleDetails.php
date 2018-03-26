<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;

class InstallBundleDetails
{
    const INSTALL_TYPE_INSTALL = 'install';

    const INSTALL_TYPE_UPGRADE = 'upgrade';

    /** @var App */
    private $app;

    /** @var string */
    private $installType;

    public function __construct($app, $installType)
    {
        $this->app         = $app;
        $this->installType = $installType;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    public function getInstallType()
    {
        return $this->installType;
    }
}
