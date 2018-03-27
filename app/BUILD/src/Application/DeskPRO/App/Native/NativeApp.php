<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native;

use Application\DeskPRO\Entity\AppInstance;

class NativeApp
{
    /**
     * @var \Application\DeskPRO\Entity\AppInstance
     */
    private $app;

    /**
     * @var NativePackageConfig
     */
    private $config;

    /**
     * @param AppInstance         $app
     * @param NativePackageConfig $config
     */
    public function __construct(AppInstance $app, NativePackageConfig $config)
    {
        $this->app    = $app;
        $this->config = $config;
    }

    /**
     * @return NativePackageConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return AppInstance
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return \Application\DeskPRO\Entity\AppPackage
     */
    public function getPackage()
    {
        return $this->app->package;
    }

    /**
     * @return string
     */
    public function getClassNamespace()
    {
        return $this->config->getClassNamespace();
    }

    /**
     * @return string
     */
    public function getNativeDir()
    {
        return $this->config->getNativeDir();
    }
}
