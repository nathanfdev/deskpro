<?php

namespace deskpro_gmail_oauth;

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
        $settingsHandler = $context->getContainer()->getSettingsHandler();
        $settingsHandler->setSettiуng('core_email.google_oauth_client_id', '');
        $settingsHandler->setSetting('core_email.google_oauth_secret', '');
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
        $settingsHandler = $context->getContainer()->getSettingsHandler();
        $settingsHandler->setSetting(
            'core_email.google_oauth_client_id',
            $context->getApp()->getSetting('gmail_oauth_client_id') ?: null
        );
        $settingsHandler->setSetting(
            'core_email.google_oauth_secret',
            $context->getApp()->getSetting('gmail_oauth_secret') ?: null
        );
    }
}
