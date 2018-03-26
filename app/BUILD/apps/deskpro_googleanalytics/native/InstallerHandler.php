<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace deskpro_googleanalytics;

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
        $handler = $context->getContainer()->getSettingsHandler();
        if (!is_array($this->settingsDef)) {
            throw new \Exception('Wrong Package settings definition');
        }
        foreach ($this->settingsDef as $set) {
            if (isset($set['name'])) {
                $handler->setSetting('core.'.$set['name'], null);
            }
        }
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
        $handler = $context->getContainer()->getSettingsHandler();
        if (!is_array($this->settingsDef)) {
            throw new \Exception('Wrong Package settings definition');
        }
        foreach ($this->settingsDef as $set) {
            if (isset($set['name'])) {
                $handler->setSetting('core.'.$set['name'], $context->getApp()->getSetting($set['name']));
            }
        }
    }
}
