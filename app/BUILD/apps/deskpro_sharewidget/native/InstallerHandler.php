<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_sharewidget;

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
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_widget',   null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_facebook', null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_twitter',  null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_linkedin', null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_gplus',    null);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSettings(InstallerContext $context)
    {
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_widget',   1);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_facebook', $context->getApp()->getSetting('show_share_facebook') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_twitter',  $context->getApp()->getSetting('show_share_twitter') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_linkedin', $context->getApp()->getSetting('show_share_linkedin') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_gplus',    $context->getApp()->getSetting('show_share_gplus') ?: null);
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
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_widget',   1);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_facebook', $context->getApp()->getSetting('show_share_facebook') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_twitter',  $context->getApp()->getSetting('show_share_twitter') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_linkedin', $context->getApp()->getSetting('show_share_linkedin') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_gplus',    $context->getApp()->getSetting('show_share_gplus') ?: null);
    }
}
