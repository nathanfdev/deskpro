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

use Orb\Util\Arrays;

class Build1400056736 extends AbstractBuild
{
	public function run()
	{
		$this->out("Upgrading escalation logs");

		$db = $this->container->getDb();

		$id_map = $this->getUpgradeData('201404', 'esc_id_map');

		if ($id_map) {
			$old_ids = implode(',', array_keys($id_map));
			$new_ids = implode(',', array_values($id_map));

			$db->exec("SET FOREIGN_KEY_CHECKS = 0");
			$db->exec("
				INSERT INTO ticket_escalation_logs (ticket_id, escalation_id, date_ran, date_criteria)
				SELECT ticket_id, trigger_id, date_ran, date_criteria FROM ticket_trigger_logs WHERE trigger_id IN ($old_ids)
			");

			// First pass is to prevent collisisions on new ids
			foreach ($id_map as $old_id => $new_id) {
				$db->update('ticket_escalation_logs', array('escalation_id' => $new_id + 5000), array('escalation_id' => $old_id));
			}

			// second pass to set the actual IDs
			foreach ($id_map as $new_id) {
				$db->update('ticket_escalation_logs', array('escalation_id' => $new_id), array('escalation_id' => $new_id + 5000));
			}

			$db->executeUpdate("DELETE FROM ticket_escalation_logs WHERE escalation_id NOT IN ($new_ids)");
			$db->exec("SET FOREIGN_KEY_CHECKS = 1");
		}

		$db->exec("DROP TABLE IF EXISTS ticket_trigger_logs");
	}
}