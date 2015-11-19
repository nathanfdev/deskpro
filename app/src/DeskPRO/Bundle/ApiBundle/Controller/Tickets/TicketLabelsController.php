<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to ticket labels.
 */
class TicketLabelsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get tickets with the given label",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_labels/{name}/tickets", name="api_ticket_labels_tickets")
     */
    public function getLabelTicketsAction($name)
    {
        $repo   = $this->getEm()->getRepository('DeskPRO:LabelTicket');
        $labels = $repo->findBy(['label' => $name]);

        $tickets = [];
        foreach ($labels as $label) {
            $tickets[$label->ticket->getId()] = $label->ticket;
        }

        return View::create(
            $this->dataSerialize(array_values($tickets)),
            Response::HTTP_OK
        );
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
