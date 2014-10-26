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

use Symfony\Component\HttpFoundation\Response;

use Orb\Util\Arrays;

/**
 * All you wanted to know about ticket statuses but frightened to ask!
 *
 * @SWG\Resource(
 * 	resourcePath="/ticket_statuses",
 * 	description="Operations about Ticket status",
 * 	basePath="/api"
 * )
 */
class TicketStatusesController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}

    /**
     * @SWG\Api(
     * 	path="/ticket_statuses/stats",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Overall ticket statistic",
     * 		notes="Tickets grouped by their status and counted",
     *		type="array",
     *
     *  )
     * )
     *
     * @return Response
     */
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

    /**
     * @SWG\Api(
     * 	path="/ticket_statuses/archived",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Return archivation settings",
     * 		notes="",
     *  )
     * )
     *
     * @return Response
     *
     */
	public function getArchivedInfoAction()
	{
		$info = array(
			'enabled'           => (bool)$this->settings->get('core_tickets.use_archive'),
			'auto_archive_time' => (int)$this->settings->get('core_tickets.auto_archive_time'),
		);

		return $this->createApiResponse(array(
			'archived_info' => $info
		));
	}

    /**
     * @return Response
     *
     * @SWG\Api(
     *  path="/ticket_statuses/archived/settings",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Save archivator settings",
     * 		notes="",
     *		type="array",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="enabled",
     *				description="Should be tickets be archived?",
     *				paramType="query",
     *				required=true,
     *				type="boolean"
     *			),
     *      @SWG\Parameter(
     *				name="auto_archive_time",
     *				description="When tickets have to be archived?",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *      )
     *  )
     * )
     */
	public function saveArchivedSettingsAction()
	{
		$this->settings->setSetting('core_tickets.use_archive', $this->in->getBoolInt('enabled'));
		$this->settings->setSetting('core_tickets.auto_archive_time', $this->in->getUint('auto_archive_time'));

		return $this->createSuccessResponse();
	}


    /**
     * @SWG\Api(
     * 	path="/ticket_statuses/archived/reset-search-tables",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Clean search status",
     * 		notes="",
     *  )
     * )
     *
     * @return Response
     *
     */
	public function resetSearchTablesAction()
	{
        /** @var \Application\DeskPRO\EntityRepository\Ticket $entityRepository*/
        $entityRepository = $this->em->getRepository('DeskPRO:Ticket');
        $entityRepository->fillSearchTable();
		return $this->createSuccessResponse();
	}

    /**
     * @SWG\Api(
     * 	path="/ticket_statuses/deleted",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Return deleted tickets autopurge settings",
     * 		notes="",
     *  )
     * )
     *
     * @return Response
     *
     */
	public function getDeletedInfoAction()
	{
		$info = array(
			'auto_purge_time' => (int)$this->settings->get('core_tickets.hard_delete_time'),
		);

		return $this->createApiResponse(array(
			'deleted_info' => $info
		));
	}

    /**
     * Purge deleted tickets manually
     * @return Response
     *
     * @SWG\Api(
     * 	path="/ticket_statuses/deleted/purge",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Purge deleted tickets manually",
     * 		notes="Will return count for purged tickets",
     *  )
     * )
     */
	public function purgeDeletedAction()
	{
		$purger = new TicketPurger($this->db);
		$count = $purger->purgeDeletedAction();

		return $this->createSuccessResponse(array(
			'count' => $count
		));
	}

    /**
     * @return Response
     *
     * @SWG\Api(
     *  path="/ticket_statuses/deleted/settings",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Save deleted tickets autopurge settings",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameter(
     *				name="auto_archive_time",
     *				description="When tickets have to be pruged automatically?",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *      )
     *  )
     * )
     */
	public function saveDeletedSettingsAction()
	{
		$this->settings->setSetting('core_tickets.hard_delete_time', $this->in->getUint('auto_purge_time'));

		return $this->createSuccessResponse();
	}

    /**
     * @SWG\Api(
     * 	path="/ticket_statuses/spam",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Return spam autodelete settings",
     * 		notes="",
     *  )
     * )
     * @return Response
     *
     */
	public function getSpamInfoAction()
	{
		$info = array(
			'auto_purge_time' => (int)$this->settings->get('core_tickets.spam_delete_time'),
		);

		return $this->createApiResponse(array(
			'spam_info' => $info
		));
	}

    /**
     * Purge spam tickets manually
     * @return Response
     *
     * @SWG\Api(
     * 	path="/ticket_statuses/spam/purge",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Purge spam tickets manually",
     * 		notes="Will return count for purged tickets",
     *  )
     * )
     */
	public function purgeSpamAction()
	{
		$purger = new TicketPurger($this->db);
		$count = $purger->purgeSpamAction();

		return $this->createSuccessResponse(array(
			'count' => $count
		));
	}

    /**
     * @return Response
     *
     * @SWG\Api(
     *  path="/ticket_statuses/spam/settings",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Save spam autopurge settings",
     * 		notes="",
     *		type="array",
     *      @SWG\Parameter(
     *				name="auto_archive_time",
     *				description="When spam tickets have to be pruged automatically?",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *      )
     *  )
     * )
     */
	public function saveSpamSettingsAction()
	{
		$this->settings->setSetting('core_tickets.spam_delete_time', $this->in->getUint('auto_purge_time'));

		return $this->createSuccessResponse();
	}
}