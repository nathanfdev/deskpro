<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_ticket_hostnames;

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
        $context->getContainer()->getSettingsHandler()->setSetting('rdns_ticket_showprops', null);
        $context->getContainer()->getSettingsHandler()->setSetting('rdns_ticket_messages', null);
        $context->getContainer()->getSettingsHandler()->setSetting('rdns_server', null);
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
        $context->getContainer()->getSettingsHandler()->setSetting('rdns_ticket_messages', 1);
        $context->getContainer()->getSettingsHandler()->setSetting('rdns_ticket_showprops', $context->getApp()->getSetting('rdns_ticket_showprops') ?: 0);
        $context->getContainer()->getSettingsHandler()->setSetting('rdns_server', $context->getApp()->getSetting('rdns_server') ?: '8.8.8.8');
    }
}
