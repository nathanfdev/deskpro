<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace deskpro_sharewidget;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\AbstractInstallerHandler;

class InstallerHandler extends AbstractInstallerHandler
{
    /**
     * {@inheritDoc}
     */
    public function install(InstallerContext $context)
    {
        $this->_doInstall($context);
    }


    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function updateSettings(InstallerContext $context)
    {
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_widget',   1);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_facebook', $context->getApp()->getSetting('show_share_facebook') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_twitter',  $context->getApp()->getSetting('show_share_twitter')  ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_linkedin', $context->getApp()->getSetting('show_share_linkedin') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_gplus',    $context->getApp()->getSetting('show_share_gplus')    ?: null);
    }


    /**
     * {@inheritDoc}
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
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_twitter',  $context->getApp()->getSetting('show_share_twitter')  ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_linkedin', $context->getApp()->getSetting('show_share_linkedin') ?: null);
        $context->getContainer()->getSettingsHandler()->setSetting('core.show_share_gplus',    $context->getApp()->getSetting('show_share_gplus')    ?: null);
    }
}
