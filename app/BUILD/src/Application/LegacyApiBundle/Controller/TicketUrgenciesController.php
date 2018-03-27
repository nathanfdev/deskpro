<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * Operations about Ticket urgencies.
 *
 * SWG\Resource(
 * 	resourcePath="/ticket_urgencies",
 * 	description="Operations about Ticket urgencies",
 * 	basePath="/api"
 * )
 *
 * @ApiModes("all")
 */
class TicketUrgenciesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * SWG\Api(
     * 	path="/ticket_urgencies",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of ticket counted urgency",
     * 		notes="",
     *		type="array",
     *  )
     * )
     */
    public function listAction()
    {
        $counts = $this->em->getRepository('DeskPRO:Ticket')->countTicketsByUrgency();

        return $this->createApiResponse(['urgency_counts' => $counts]);
    }
}
