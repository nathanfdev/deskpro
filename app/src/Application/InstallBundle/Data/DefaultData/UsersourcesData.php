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
 * @subpackage
 */

namespace Application\InstallBundle\Data\DefaultData;


use Application\DeskPRO\Entity\Usersource;

class UsersourcesData extends AbstractDefaultData
{
	public function runInstallViaUpgrade()
	{
		// do nothing because the db upgrade script will do this (along with other things) for us
	}


	public function runInstall()
	{
		$this->installDeskproUsersource();
	}


	public function runReset()
	{
	}


	public function runSync()
	{
	}


	private function installDeskproUsersource()
	{
		$enabled = $this->getContainer()->getSetting('core.deskpro_source_enabled') ? 1 : 0;

		$types = array('user', 'agent');
		foreach($types as $type) {
			$deskProUsers                = new Usersource();
			$deskProUsers->type          = $type;
			$deskProUsers->source_type   = 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO';
			$deskProUsers->is_enabled    = $enabled;
			$deskProUsers->display_order = -10; // ensure #1 order (initially!)
			$deskProUsers->title         = 'DeskPRO';
			$deskProUsers->options       = array();
			$this->getEm()->persist($deskProUsers);
		}

		$this->getEm()->flush();
	}
}
 