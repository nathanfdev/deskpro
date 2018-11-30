<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\TicketPurger;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * All you wanted to know about ticket statuses but frightened to ask!
 *
 * SWG\Resource(
 * 	resourcePath="/ticket_statuses",
 * 	description="Operations about Ticket status",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class TicketStatusesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // get-status
    //###################################################################################################################

    /**
     * SWG\Api(
     * 	path="/ticket_statuses/stats",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Overall ticket statistic",
     * 		notes="Tickets grouped by their status and counted",
     *		type="array",.
     *
     *  )
     * )
     *
     * @return response
     */
    public function getStatsAction()
    {
        $stats = $this->db->fetchAllKeyValue('
            SELECT status, COUNT(*)
            FROM tickets
            GROUP BY status
        ');

        $h_stats = $this->db->fetchAllKeyValue("
            SELECT ts.sys_id, COUNT(*)
            FROM tickets t
            INNER JOIN ticket_statuses ts ON t.ticket_status_id = ts.id
            WHERE t.status = 'hidden' AND ts.sys_id IN ('spam', 'deleted')
            GROUP BY ts.sys_id
        ");
        foreach ($h_stats as $s => $c) {
            $stats['hidden_'.$s] = $c;
        }

        $stats = Arrays::castToType($stats, 'int', 'string');

        return $this->createApiResponse(['status_stats' => $stats]);
    }

    //###################################################################################################################
    // get-archived-info
    //###################################################################################################################

    /**
     * SWG\Api(
     * 	path="/ticket_statuses/archived",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Return archivation settings",
     * 		notes="",
     *  )
     * ).
     *
     * @return response
     */
    public function getArchivedInfoAction()
    {
        $info = [
            'enabled'           => (bool) $this->settings->get('core_tickets.use_archive'),
            'auto_archive_time' => (int) $this->settings->get('core_tickets.auto_archive_time'),
        ];

        return $this->createApiResponse([
            'archived_info' => $info,
        ]);
    }

    //###################################################################################################################
    // save-archived-settings
    //###################################################################################################################

    /**
     * @return response
     *
     * SWG\Api(
     *  path="/ticket_statuses/archived/settings",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Save archivator settings",
     * 		notes="",
     *		type="array",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="enabled",
     *				description="Should be tickets be archived?",
     *				paramType="query",
     *				required=true,
     *				type="boolean"
     *			),
     *      SWG\Parameter(
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
        $enabled = $this->in->getBoolInt('enabled');
        $this->settings->setSetting('core_tickets.use_archive', $enabled);
        $this->settings->setSetting('core_tickets.auto_archive_time', $this->in->getUint('auto_archive_time'));

        if (!$enabled) {
            $this->em->getConnection()->executeQuery('
                UPDATE tickets
                SET status = ?
                WHERE status = ?
            ', [Ticket::STATUS_RESOLVED, Ticket::STATUS_ARCHIVED]);
            $this->em->getRepository('DeskPRO:Ticket')->fillSearchTable();
        }

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/ticket_statuses/archived/reset-search-tables",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Clean search status",
     * 		notes="",
     *  )
     * ).
     *
     * @return response
     */
    public function resetSearchTablesAction()
    {
        $this->em->getRepository('DeskPRO:Ticket')->fillSearchTable();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get-deleted-info
    //###################################################################################################################

    /**
     * SWG\Api(
     * 	path="/ticket_statuses/deleted",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Return deleted tickets autopurge settings",
     * 		notes="",
     *  )
     * ).
     *
     * @return response
     */
    public function getDeletedInfoAction()
    {
        $info = [
            'auto_purge_time' => (int) $this->settings->get('core_tickets.hard_delete_time'),
        ];

        return $this->createApiResponse([
            'deleted_info' => $info,
        ]);
    }

    /**
     * Purge deleted tickets manually.
     *
     * @return response
     *
     * SWG\Api(
     * 	path="/ticket_statuses/deleted/purge",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Purge deleted tickets manually",
     * 		notes="Will return count for purged tickets",
     *  )
     * )
     */
    public function purgeDeletedAction()
    {
        $purger = new TicketPurger($this->db);
        $count  = $purger->purgeDeletedAction();

        return $this->createSuccessResponse([
            'count' => $count,
        ]);
    }

    //###################################################################################################################
    // save-deleted-settings
    //###################################################################################################################

    /**
     * @return response
     *
     * SWG\Api(
     *  path="/ticket_statuses/deleted/settings",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Save deleted tickets autopurge settings",
     * 		notes="",
     *		type="array",
     *      SWG\Parameter(
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

    //###################################################################################################################
    // get-spam-info
    //###################################################################################################################

    /**
     * SWG\Api(
     * 	path="/ticket_statuses/spam",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Return spam autodelete settings",
     * 		notes="",
     *  )
     * ).
     *
     * @return response
     */
    public function getSpamInfoAction()
    {
        $info = [
            'auto_purge_time' => (int) $this->settings->get('core_tickets.spam_delete_time'),
        ];

        return $this->createApiResponse([
            'spam_info' => $info,
        ]);
    }

    /**
     * Purge spam tickets manually.
     *
     * @return response
     *
     * SWG\Api(
     * 	path="/ticket_statuses/spam/purge",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Purge spam tickets manually",
     * 		notes="Will return count for purged tickets",
     *  )
     * )
     */
    public function purgeSpamAction()
    {
        $purger = new TicketPurger($this->db);
        $count  = $purger->purgeSpamAction();

        return $this->createSuccessResponse([
            'count' => $count,
        ]);
    }

    //###################################################################################################################
    // save-spam-settings
    //###################################################################################################################

    /**
     * @return response
     *
     * SWG\Api(
     *  path="/ticket_statuses/spam/settings",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Save spam autopurge settings",
     * 		notes="",
     *		type="array",
     *      SWG\Parameter(
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
