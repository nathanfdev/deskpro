<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Zapier\TicketUpdate;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ZapierController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/zapier")
 */
class ZapierController extends BaseController
{
    /**
     * Gather specific info about authentication.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="ping and if it not the case install Zapier app",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noOutput=true
     * )
     * @Rest\Get("/ping")
     */
    public function pingAction()
    {
        return View::create([], 200);
    }

    /**
     * @ApiDoc(
     *     section="Apps",
     *     description="Provide examples for Zapier webhooks",
     *     output="array"
     * )
     * @Rest\Get("/example/{action}", requirements={
     *     "action"="(new_ticket_reply|ticket_update)"
     * })
     *
     * @param string $action
     *
     * @return JsonResponse
     */
    public function exampleAction($action)
    {
        $context = new SideloadSerializationContext();
        $context->setIncludes(['person', 'brand', 'ticket']);
        $context->setInlineSideloads(true);
        switch ($action) {
            case 'new_ticket_reply':
                $ticketMessage = $this->getManager()->getRepository(TicketMessage::class)->findOneBy([], ['id' => 'DESC']);

                $output = $this->get('serializer')->toArray(new ApiWrapper($ticketMessage), $context);

                return new JsonResponse($output);
            case 'ticket_update':
                $ticketLog    = $this->getManager()->getRepository(TicketLog::class)->findOneBy([], ['id' => 'DESC']);
                $ticket       = $ticketLog->getTicket();
                $ticketUpdate = new TicketUpdate();
                $ticketUpdate->setTicket($ticket);
                $details                = $ticketLog->getDetails();
                $details['action_type'] = $ticketLog->getActionType();
                $ticketUpdate->setChanges([$details]);
                $ticketUpdate->setPerformer($ticketLog->getPerson());

                $output = $this->get('serializer')->toArray(new ApiWrapper($ticketUpdate), $context);

                return new JsonResponse($output);
            default:
        }
    }
}
