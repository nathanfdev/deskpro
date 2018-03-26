<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\InstallerHandler;

interface InstallerHandlerInterface
{
    /**
     * @param InstallerContext $context
     * @param array            $settings
     *
     * @return array
     */
    public function processSettings(InstallerContext $context, array $settings);

    /**
     * @param InstallerContext $context
     * @param array            $settings
     *
     * @return array
     */
    public function validateSettings(InstallerContext $context, array $settings);

    /**
     * @param InstallerContext $context
     */
    public function install(InstallerContext $context);

    /**
     * @param InstallerContext $context
     */
    public function uninstall(InstallerContext $context);

    /**
     * @param InstallerContext $context
     */
    public function updateSettings(InstallerContext $context);

    /**
     * @param InstallerContext $context
     */
    public function updatePackage(InstallerContext $context);
}
