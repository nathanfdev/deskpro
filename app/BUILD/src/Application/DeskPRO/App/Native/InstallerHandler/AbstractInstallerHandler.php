<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\InstallerHandler;

abstract class AbstractInstallerHandler implements InstallerHandlerInterface
{
    /** @var array */
    protected $settingsDef;

    public function __construct($settingsDef = [])
    {
        $this->settingsDef = $settingsDef;
    }

    /**
     * @param InstallerContext $context
     * @param array            $settings
     *
     * @return array
     */
    public function processSettings(InstallerContext $context, array $settings)
    {
        return $settings;
    }

    /**
     * @param InstallerContext $context
     * @param array            $settings
     *
     * @return array
     */
    public function validateSettings(InstallerContext $context, array $settings)
    {
        return $settings;
    }

    /**
     * @param InstallerContext $context
     */
    public function install(InstallerContext $context)
    {
    }

    /**
     * @param InstallerContext $context
     */
    public function uninstall(InstallerContext $context)
    {
    }

    /**
     * @param InstallerContext $context
     */
    public function updateSettings(InstallerContext $context)
    {
    }

    /**
     * @param InstallerContext $context
     */
    public function updatePackage(InstallerContext $context)
    {
    }
}
