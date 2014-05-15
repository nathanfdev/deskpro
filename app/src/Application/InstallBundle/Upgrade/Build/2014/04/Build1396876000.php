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

class Build1396876000 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();
		$me = $this;
		$this->out("Saving table data for future steps");

		$fn_save_table = function($table) use ($db, $me) {
			$recs = $db->fetchAll("SELECT * FROM $table");
			if (!$recs) $recs = array();
			$me->saveUpgradeData('201404', $table, $recs);
		};

		$fn_save_table('web_hooks');
		$fn_save_table('email_gateway_addresses');
		$fn_save_table('email_gateways');
		$fn_save_table('email_transports');
		$fn_save_table('ticket_page_display');
		$fn_save_table('ticket_filters');
		$fn_save_table('ticket_triggers');
		$fn_save_table('slas');
		$fn_save_table('sla_people');
		$fn_save_table('sla_organizations');
		$fn_save_table('departments');
		$fn_save_table('widgets');
		$fn_save_table('plugins');
		$fn_save_table('usersources');
	}
}