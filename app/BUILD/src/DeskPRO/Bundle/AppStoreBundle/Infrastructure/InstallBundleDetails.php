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

    /** @var bool */
    private $forceConfiguration;

    /**
     * InstallBundleDetails constructor.
     * @param App $app
     * @param string $installType
     * @param bool $forceConfiguration
     */
    public function __construct(App $app, $installType, $forceConfiguration)
    {
        $this->app         = $app;
        $this->installType = $installType;
        $this->forceConfiguration = $forceConfiguration;
    }

    /**
     * @return bool
     */
    public function reloadRequired()
    {
        return $this->installType === InstallBundleDetails::INSTALL_TYPE_UPGRADE || $this->getForceConfiguration();
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return string
     */
    public function getInstallType()
    {
        return $this->installType;
    }

    /**
     * @return bool
     */
    public function getForceConfiguration()
    {
        return $this->forceConfiguration;
    }
}
