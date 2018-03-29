<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;
use Symfony\Component\HttpFoundation\Response;

/**
 * Operations about activity.
 *
 * SWG\Resource(
 * 	resourcePath="/activity",
 * 	description="Operations about activity",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class ActivityController extends AbstractController
{
    /**
     * @param int $since
     *
     * @return Response
     *
     * SWG\Api(
     * 	path="/activity/{since}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get activity since given time",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="since",
     *				description="Since what time",
     *				paramType="path",
     *				required=true,
     *				type="integer",
     *			),
     *      )
     *  )
     * )
     */
    public function getActivityAction($since)
    {
        if (!$since) {
            $alert_recs = $this->em->createQuery('
                SELECT a
                FROM DeskPRO:AgentAlert a
                WHERE a.person = ?0 AND a.is_dismissed = 0
                ORDER BY a.id DESC
			')->setParameters([$this->person])->setMaxResults(100)->execute();
        } else {
            $alert_recs = $this->em->createQuery('
                SELECT a
                FROM DeskPRO:AgentAlert a
                WHERE a.person = ?0 AND a.id >= ?1 AND a.is_dismissed = 0
                ORDER BY a.id DESC
			')->setParameters([$this->person, $since])->setMaxResults(100)->execute();
        }

        $alerts = [];
        foreach ($alert_recs as $alert) {
            $alerts[] = [
                'id'                 => $alert->getId(),
                'type'               => $alert->typename,
                'date_created'       => $alert->date_created->format('Y-m-d H:i:s'),
                'date_created_ts'    => $alert->date_created->getTimestamp(),
                'date_created_ts_ms' => $alert->date_created->getTimestamp() * 1000,
                'data'               => $this->container->getAgentAlertSender()->getDataArray($alert),
            ];
        }

        $last_id = $this->db->fetchColumn('SELECT id FROM agent_alerts ORDER BY id DESC LIMIT 1');

        return $this->createApiResponse(['last_id' => $last_id, 'alerts' => $alerts]);
    }

    /**
     * @throws \Exception
     *
     * SWG\Api(
     * 	path="/activity/dismiss",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Dismiss activities by their IDs",
     * 		notes="",
     *		type="array",
     *      SWG\Parameters (
     *          SWG\Parameter(
     *				name="dismiss_ids",
     *				description="Escalation ID",
     *				paramType="path",
     *				required=true,
     *				type="string|integer[]",
     *			),
     *      )
     *  )
     * )
     *
     * @return Response
     */
    public function dismissAction()
    {
        // Could be a json encoded array
        if (isset($_REQUEST['dismiss_ids']) && !is_array($_REQUEST['dismiss_ids'])) {
            $alert_ids = $this->in->getString('dismiss_ids');
            $alert_ids = @json_decode($alert_ids, true);

            if ($alert_ids) {
                $alert_ids = Arrays::castToType($alert_ids, 'int', 'discard');
                $alert_ids = array_unique($alert_ids);
            }

        // or a regular posted array
        } else {
            $alert_ids = $this->in->getCleanValueArray('dismiss_ids', 'int', 'discard');
            $alert_ids = Arrays::removeFalsey($alert_ids);
            $alert_ids = array_unique($alert_ids);
        }

        if ($alert_ids) {
            if (in_array(-1, $alert_ids)) {
                $this->db->executeUpdate('
                    UPDATE agent_alerts
                    SET is_dismissed = 1
                    WHERE person_id = ?
                ', [$this->person->getId()]);
            } else {
                $ids_in = implode(',', $alert_ids);
                $this->db->executeUpdate("
                    UPDATE agent_alerts
                    SET is_dismissed = 1
                    WHERE person_id = ? AND id IN ($ids_in)
                ", [$this->person->getId()]);
            }
        }

        return $this->createApiResponse(['success' => true]);
    }
}
