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
 * Provides API access to the ticket flags.
 */
class TicketFlagsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a list of ticket flags",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_stars", name="api_ticket_flags")
     */
    public function cgetAction()
    {
        $flags = $this->get('data.ticketflags');
        return View::create(
            $this->DataSerialize(new PrimitiveArray($flags->getFlags())),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get the counts of tickets marked with each star",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_stars/all/counts", name="api_ticket_flag_all_counts")
     */
    public function getTicketFlagsCounts()
    {
        $flags_service = $this->get('data.ticketflags');

        $counts = [];
        foreach($flags_service->getFlags() as $flag) {
            $counts[] = [
                'star' => $flag,
                'count' => count($flags_service->getAllRecordsForFlag($this->getUser()->getId(), $flag)),
            ];
        }

        return View::create(
            $this->DataSerialize(new PrimitiveArray($counts)),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get the count of tickets marked with each flag",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_stars/{star}/count", name="api_ticket_flag_count")
     */
    public function getTicketFlagCount($star)
    {
        $tickets = $this->get('data.ticketflags')->getAllRecordsForFlag($this->getUser()->getId(), $star);

        return View::create(
            $this->DataSerialize(new PrimitiveArray([
                'count' => count($tickets),
            ])),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get the tickets for a star",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/ticket_stars/{star}/tickets", name="api_ticket_flag_tickets")
     */
    public function getTicketFlagTickets($star)
    {
        $tickets = $this->get('data.ticketflags')->getAllTicketsForFlag($this->getUser()->getId(), $star);

        return View::create(
            $this->DataSerialize($tickets),
            Response::HTTP_OK
        );
    }
}
