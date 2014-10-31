<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace deskpro_jira2;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\AbstractInstallerHandler;
use Application\DeskPRO\JIRA\OAuthWrapper;
use Application\DeskPRO\Service\JIRA;

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
		$settings = $context->getContainer()->getSettingsHandler();

		$settings->setSetting(JIRA::PARAM_ENABLED, null);
		$settings->setSetting(JIRA::PARAM_COMMENTS, null);
		$settings->setSetting(OAuthWrapper::PARAM_URL, null);
		$settings->setSetting(OAuthWrapper::PARAM_CONSUMER, null);
		$settings->setSetting(OAuthWrapper::PARAM_TOKENS, null);
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
		$url = $context->getApp()->getSetting('url');
		$consumer = $context->getApp()->getSetting('consumer_key');
		$comments = $context->getApp()->getSetting('comments');
		$meta = $context->getApp()->getSetting('meta');

		if (!$url || !$consumer) {
			$enabled = 0;
		}

		$settings = $context->getContainer()->getSettingsHandler();

		$settings->setSetting(JIRA::PARAM_ENABLED, $enabled);
		$settings->setSetting(JIRA::PARAM_COMMENTS, $comments);
		$settings->setSetting(OAuthWrapper::PARAM_URL, $url);
		$settings->setSetting(OAuthWrapper::PARAM_CONSUMER, $consumer);

		if ($meta) {
			$context->getContainer()->get('dp.jira')->updateMeta($meta);
		}
	}
}
