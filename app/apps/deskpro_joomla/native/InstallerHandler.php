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

namespace deskpro_joomla;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\InstallerHandlerInterface;

class InstallerHandler implements InstallerHandlerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function install(InstallerContext $context)
	{
		$context->getDb()->insert('usersources', array(
			'app_id' => $context->getApp()->id,
			'title' => $context->getApp()->title,
			'source_type' => 'app',
			'lost_password_url' => '@app_settings.lost_password_url',
			'options' => array('joomla_url' => '@app_settings.joomla_url', 'joomla_secret' => '@app_settings.joomla_secret'),
			'is_enabled' => true
		));
	}


	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstallerContext $context)
	{

	}


	/**
	 * {@inheritDoc}
	 */
	public function update(InstallerContext $context)
	{

	}
}
