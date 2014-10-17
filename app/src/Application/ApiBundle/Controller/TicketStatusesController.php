<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Tickets\TicketPurger;
use Orb\Util\Arrays;

class TicketStatusesController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


	####################################################################################################################
	# get-status
	####################################################################################################################

	public function getStatsAction()
	{
		$stats = $this->db->fetchAllKeyValue("
			SELECT status, COUNT(*)
			FROM tickets
			GROUP BY status
		");

		$h_stats = $this->db->fetchAllKeyValue("
			SELECT hidden_status, COUNT(*)
			FROM tickets
			WHERE status = 'hidden' AND hidden_status IS NOT NULL
			GROUP BY hidden_status
		");
		foreach ($h_stats as $s => $c) {
			$stats['hidden_' . $s] = $c;
		}

		$stats = Arrays::castToType($stats, 'int', 'string');

		return $this->createApiResponse(array('status_stats' => $stats));
	}

	####################################################################################################################
	# get-closed-info
	####################################################################################################################

	public function getClosedInfoAction()
	{
		$info = array(
			'enabled'           => (bool)$this->settings->get('core_tickets.use_archive'),
			'auto_archive_time' => (int)$this->settings->get('core_tickets.auto_archive_time'),
		);

		return $this->createApiResponse(array(
			'closed_info' => $info
		));
	}

	####################################################################################################################
	# save-closed-settings
	####################################################################################################################

	public function saveClosedSettingsAction()
	{
		$this->settings->setSetting('core_tickets.use_archive', $this->in->getBoolInt('enabled'));
		$this->settings->setSetting('core_tickets.auto_archive_time', $this->in->getUint('auto_archive_time'));

		return $this->createSuccessResponse();
	}

	public function resetSearchTablesAction()
	{
		$this->em->getRepository('DeskPRO:Ticket')->fillSearchTable();
		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get-deleted-info
	####################################################################################################################

	public function getDeletedInfoAction()
	{
		$info = array(
			'auto_purge_time' => (int)$this->settings->get('core_tickets.hard_delete_time'),
		);

		return $this->createApiResponse(array(
			'deleted_info' => $info
		));
	}

	public function purgeDeletedAction()
	{
		$purger = new TicketPurger($this->db);
		$count = $purger->purgeDeletedAction();

		return $this->createSuccessResponse(array(
			'count' => $count
		));
	}

	####################################################################################################################
	# save-deleted-settings
	####################################################################################################################

	public function saveDeletedSettingsAction()
	{
		$this->settings->setSetting('core_tickets.hard_delete_time', $this->in->getUint('auto_purge_time'));

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get-spam-info
	####################################################################################################################

	public function getSpamInfoAction()
	{
		$info = array(
			'auto_purge_time' => (int)$this->settings->get('core_tickets.spam_delete_time'),
		);

		return $this->createApiResponse(array(
			'spam_info' => $info
		));
	}

	public function purgeSpamAction()
	{
		$purger = new TicketPurger($this->db);
		$count = $purger->purgeSpamAction();

		return $this->createSuccessResponse(array(
			'count' => $count
		));
	}

	####################################################################################################################
	# save-spam-settings
	####################################################################################################################

	public function saveSpamSettingsAction()
	{
		$this->settings->setSetting('core_tickets.spam_delete_time', $this->in->getUint('auto_purge_time'));

		return $this->createSuccessResponse();
	}
}