<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_gravatar;

use Application\DeskPRO\App\Native\InstallerHandler\AbstractInstallerHandler;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;

class InstallerHandler extends AbstractInstallerHandler
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallerContext $context)
    {
        $this->_doInstall($context);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(InstallerContext $context)
    {
        $context->getContainer()->getSettingsHandler()->setSetting('core.use_gravatar', null);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSettings(InstallerContext $context)
    {
        $this->_doInstall($context);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePackage(InstallerContext $context)
    {
        $this->_doInstall($context);
    }

    /**
     * @param InstallerContext $context
     */
    private function _doInstall(InstallerContext $context)
    {
        $context->getContainer()->getSettingsHandler()->setSetting('core.use_gravatar', 1);
    }
}
