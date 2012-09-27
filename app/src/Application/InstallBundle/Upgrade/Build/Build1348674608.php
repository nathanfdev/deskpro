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

class Build1348674608 extends AbstractBuild
{
	public function run()
	{
		$this->out("Update triggers");

		$time_trigger_options = $this->container->getDb()->fetchAll("
			SELECT id, event_trigger, event_trigger_option
			FROM ticket_triggers
			WHERE event_trigger_option != ''
		");

		// Restore proper time trigger option
		foreach ($time_trigger_options as $info) {
			$opt = array('time' => $info['event_trigger_option']);
			$opt = serialize($opt);

			$event = str_replace('time_', 'time.', $info['event_trigger']);

			$update = array(
				'event_trigger' => $event,
				'time' => $opt,
			);

			$this->container->getDb()->update('ticket_triggers', $update, array('id' => $info['id']));
		}

		$this->execMutateSql("ALTER TABLE ticket_triggers ADD event_trigger_options LONGBLOB DEFAULT NULL COMMENT '(DC2Type:array)', ADD terms_any LONGBLOB NOT NULL COMMENT '(DC2Type:array)', DROP event_trigger_option");
	}
}