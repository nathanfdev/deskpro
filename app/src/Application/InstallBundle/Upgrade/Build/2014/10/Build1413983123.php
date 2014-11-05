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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1413983123 extends AbstractBuild
{
	public function run()
	{
		$this->out("Renaming closed to archived...");

		$this->out("-> permissions");
		$this->execMutateSql("UPDATE permissions SET name = 'agent_tickets.modify_set_archived' WHERE name = 'agent_tickets.modify_set_closed'");
		$this->execMutateSql("DELETE FROM permissions_cache");
		$this->execMutateSql("DELETE FROM result_cache");

		$this->out("-> rename filter");
		$this->execMutateSql("UPDATE ticket_filters SET sys_name = 'archive_archived' WHERE sys_name = 'archive_closed'");

		$this->out("-> filter terms");
		$this->execMutateSql("UPDATE ticket_filters SET terms = REPLACE(terms, 'date_closed', 'date_archived')");
		$this->execMutateSql("UPDATE ticket_filters SET terms = REPLACE(terms, 'closed', 'archived')");

		$this->out("-> escalation terms");
		$this->execMutateSql("UPDATE ticket_escalations SET terms = REPLACE(terms, 'date_closed', 'date_archived'), terms_any = REPLACE(terms_any, 'date_closed', 'date_archived')");

		$this->out("-> escalation actions");
		$this->execMutateSql("UPDATE ticket_escalations SET actions = REPLACE(actions, 'closed', 'archived')");

		$this->out("-> trigger terms");
		$this->execMutateSql("UPDATE ticket_triggers SET terms = REPLACE(terms, 'date_closed', 'date_archived')");
		$this->execMutateSql("UPDATE ticket_triggers SET terms = REPLACE(terms, 'closed', 'archived')");

		$this->out("-> trigger actions");
		$this->execMutateSql("UPDATE ticket_triggers SET actions = REPLACE(actions, 'closed', 'archived')");

		$this->out("-> macro actions");
		$macros = $this->container->getDb()->fetchAllKeyValue("SELECT id, actions FROM ticket_macros");
		foreach ($macros as $id => $val) {
			if (strpos($val, 'closed') === false) {
				continue;
			}

			// This song and dance is because we cant just str_replace in serialized
			// array (length change and would screw it up)
			$val = @unserialize($val);
			if (!$val) continue;
			$val = @json_encode($val);
			$val = str_replace('closed', 'archived', $val);
			$val = @json_decode($val, true);
			if ($val) {
				$val = serialize($val);
				$this->container->getDb()->executeQuery("UPDATE ticket_macros SET actions = ? WHERE id = ?", array($val, $id));
			}
		}

		$this->out("-> reports");
		$this->execMutateSql("UPDATE report_builder SET query = REPLACE(query, 'date_closed', 'date_archived')");
		$this->execMutateSql("UPDATE report_builder SET query = REPLACE(query, 'closed', 'archived')");

		$this->out("-> tickets");
		$this->execMutateSql("UPDATE tickets SET status = 'archived', hidden_status = NULL WHERE status = 'closed'");
	}
}