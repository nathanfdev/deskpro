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
 */

namespace Application\DeskPRO\AuditLog;

use Application\DeskPRO\App;
use Application\DeskPRO\AuditLog\AuditWriter\AuditDbWriter;
use Application\DeskPRO\AuditLog\AuditWriter\AuditFileWriter;

class AuditManagerFactory
{
	public static function getAuditManager()
	{
		$audit_manager  = new AuditManager();

		if (dp_get_config('debug.write_audit_log_file')) {
			$audit_writer   = new AuditFileWriter(dp_get_log_dir() . '/audit.log');
			$audit_manager->addWriter($audit_writer);
		}

		if (class_exists('DpShutdown', false)) {
			\DpShutdown::add(function() use ($audit_manager) {
				$audit_manager->flushLogs();
			});
		}

		if (!(defined('DP_INTERFACE') && DP_INTERFACE == 'api')) {
			$audit_manager->disable();
		}

		return $audit_manager;
	}

	public static function getAuditDbWriter()
	{
		return new AuditDbWriter();
	}

	public static function getAuditListener(AuditManager $audit_manager)
	{
		$audit_defs     = require(DP_ROOT.'/sys/config/auditlog-defs.php');
		$audit_listener = new AuditDoctrineListener($audit_manager, $audit_defs);

		if (!(defined('DP_INTERFACE') && DP_INTERFACE == 'api')) {
			$audit_listener->disable();
		} else {
			$audit_listener->setFilterFn(function() {
				if (!App::getCurrentPerson() || !App::getCurrentPerson()->is_agent) {
					return false;
				}
				return true;
			});
		}

		return $audit_listener;
	}
}