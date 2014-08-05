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

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\Usergroup;

class Build1404985598 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();
		$em = $this->container->getEm();

		$db->update('usergroups', array('title' => '[Custom] All Permissions'), array('title' => 'All Permissions'));
		$db->update('usergroups', array('title' => '[Custom] All Non-Destructive Permissions'), array('title' => 'All Non-Destructive Permissions'));

		$g = new Usergroup();
		$g->title          = 'All Permissions';
		$g->note           = 'Special agent group which always has all permissions enabled.';
		$g->is_agent_group = true;
		$g->sys_name       = 'agent_all_perms';
		$g->is_enabled     = true;
		$em->persist($g);

		$g = new Usergroup();
		$g->title          = 'All Non-Destructive Permissions';
		$g->note           = 'Special agent group which always has all permissions enabled except those that can be desctructive (e.g., deleting).';
		$g->is_agent_group = true;
		$g->sys_name       = 'agent_all_safe_perms';
		$g->is_enabled     = true;
		$em->persist($g);

		$em->flush();
	}
}