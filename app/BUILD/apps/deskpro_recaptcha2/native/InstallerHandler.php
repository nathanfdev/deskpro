<?php

namespace deskpro_recaptcha2;

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
        $context->getContainer()->getSettingsHandler()->setSetting('core.use_recaptcha2', false);
        $context->getContainer()->getSettingsHandler()->setSetting('core.recaptcha2_public_key', '');
        $context->getContainer()->getSettingsHandler()->setSetting('core.recaptcha2_secret_key', '');
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
        $context->getContainer()->getSettingsHandler()->setSetting('core.use_recaptcha2', true);
        $context->getContainer()->getSettingsHandler()->setSetting('core.recaptcha2_site_key', $context->getApp()->getSetting('recaptcha2_site_key') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.recaptcha2_secret_key', $context->getApp()->getSetting('recaptcha2_secret_key') ?: null);
    }
}
