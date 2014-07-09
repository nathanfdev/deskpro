<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace deskpro_jira;

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
		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.enabled', null);
	}


	/**
	 * {@inheritDoc}
	 */
	public function updateSettings(InstallerContext $context)
	{
		$this->_doInstall($context);
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
		$enabled = 1;
		if (!$context->getApp()->getSetting('jira_url') || !$context->getApp()->getSetting('jira_username') || !$context->getApp()->getSetting('jira_password')) {
			$enabled = 0;
		}

		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.enabled',        $enabled);
		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.baseUrl',        rtrim($context->getApp()->getSetting('jira_url'), '/') . '/');
		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.username',       $context->getApp()->getSetting('jira_username'));
		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.password',       $context->getApp()->getSetting('jira_password'));
		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.defaultProject', $context->getApp()->getSetting('jira_default_project'));
		$context->getContainer()->getSettingsHandler()->setSetting('core.apps_jira.defaultTags',    $context->getApp()->getSetting('jira_default_tags'));
	}
}
