<?php

/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Aws\CloudWatch\Exception\InvalidFormatException;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Exception\UnknownTicketFlagException;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;

use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;

/**
 * API access to ticket labels.
 */
class TicketStatusesController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get all available labels for tickets, sorted alphabetically",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_statuses", name="api_ticket_statuses")
     */
    public function cgetAction()
    {
        $service = $this->get('data.ticketstatuses');

        return View::create(
            $this->DataSerialize(new PrimitiveArray($service->getStatuses())),
            Response::HTTP_OK
        );
    }

    /**
     * Retrieve the tickets with the given label.
     * @Get("/ticket_statuses/{status}/tickets", name="api_ticket_statuses_tickets")
     */
    public function getTicketsForStatus($status)
    {
        $service = $this->get('data.ticketstatuses');

        return View::create(
            $this->DataSerialize($service->getAllTicketsForStatus($status)),
            Response::HTTP_OK
        );
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
