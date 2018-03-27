<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLinks\TicketLinkType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLinks\TicketUnlinkType;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LinkedTickets;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketLinksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{ticket}/links")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LinkedTickets")
 */
class TicketLinksController extends BaseController
{
    use TicketSaveTrait;

    /**
     * @ApiDoc(
     *     description="link two tickets",
     *     statusCodes={
     *         201="Tickets was linked successfully",
     *         400="You are trying to link ticket to itself",
     *         404={
     *             "Ticket with 'ticketId' wasn't found",
     *             "Ticket with 'link_ticket_id' wasn't found",
     *         }
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketLinks\TicketLinkType"
     *     }
     * )
     *
     * @param Request $request
     * @param Ticket  $ticket
     *
     * @return View
     *
     * @Rest\Post("")
     */
    public function postAction(Request $request, Ticket $ticket)
    {
        return $this->handleForm($request, $ticket, TicketLinkType::class);
    }

    /**
     * @ApiDoc(
     *     description="get tickets linked with ticket under provided id",
     *     requirements={
     *         {"name"="ticketId", "requirement"="\d+", "dataType"="integer", "description"="ticket to find id"},
     *     },
     *     statusCodes={
     *         200="Returned in case of successful request",
     *         404="Ticket with specified id wasn't found",
     *     }
     * )
     *
     * @param Ticket $ticket
     *
     * @Rest\Get("", name="api_tickets_link_list")
     *
     * @return View
     */
    public function listAction(Ticket $ticket)
    {
        return View::create($this->wrap(new LinkedTickets($ticket)));
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="delete relation between two tickets",
     *     requirements={
     *         {"name"="ticketId", "requirement"="\d+", "dataType"="integer", "description"="base ticket"},
     *         {"name"="unlinkTicketId", "requirement"="\d+", "dataType"="integer", "description"="ticket to unlink"},
     *         {
     *             "name"="link_type",
     *             "requirement"="parent|sibling|child",
     *             "dataType"="string",
     *             "description"="you have to specify relation type to unlink tickets properly"},
     *     },
     *     statusCodes={
     *         201="Tickets was unlinked successfully",
     *         400={
     *             "You are trying to unlink ticket from itself",
     *             "Wrong relation type",
     *         },
     *         404={
     *             "Ticket with 'ticketId' wasn't found",
     *             "Ticket with 'unlinkTicketId' wasn't found",
     *         },
     *     }
     * )
     *
     * @param Ticket  $ticket
     * @param Request $request
     *
     * @Rest\Delete("")
     *
     * @return View
     */
    public function deleteAction(Request $request, Ticket $ticket)
    {
        return $this->handleForm($request, $ticket, TicketUnlinkType::class);
    }

    /**
     * @param Request $request
     * @param Ticket  $ticket
     * @param string  $formType
     *
     * @return View
     */
    protected function handleForm(Request $request, Ticket $ticket, $formType)
    {
        $form = $this->createForm($formType, $ticket);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->saveTicket($ticket);
        if ($form->has('link_ticket')) {
            $this->saveTicket($form->get('link_ticket')->getData());
        }

        return View::create(null, Response::HTTP_NO_CONTENT, [
            'Location' => $this->generateUrl('api_tickets_link_list', ['ticket' => $ticket->getId()]),
        ]);
    }
}
