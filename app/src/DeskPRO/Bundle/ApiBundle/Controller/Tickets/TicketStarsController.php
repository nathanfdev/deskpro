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
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\Entity\TicketStar;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API access to the ticket flags.
 */
class TicketStarsController extends BaseController
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
        $stars = [];

        for ($i = 1; $i <= 7; ++$i) {
            $stars[] = [
                'id'    => $i,
                'name'  => TicketStar::idToColorName($i),
                'color' => TicketStar::idToColorCode($i),
            ];
        }

        return View::create(
            $this->dataSerialize(new PrimitiveArray($stars)),
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
     * @Get("/ticket_stars_count", name="api_ticket_flag_all_counts")
     */
    public function getTicketFlagsCounts()
    {
        $flags_service = $this->get('data.ticketflags');

        $count = Count::fromValue(0);
        foreach ($flags_service->getFlags() as $i => $color) {
            $flag_id = $i + 1;

            $flag_count = count($flags_service->getAllRecordsForFlag($this->getUser()->getId(), $flag_id));
            $count->addNested($flag_count, $flag_id, true);
        }

        return View::create($this->createRepresentation($count), Response::HTTP_OK);
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

        return View::create($this->createRepresentation(Count::fromValue(count($tickets))), Response::HTTP_OK);
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
        /** @var \DeskPRO\Bundle\AppBundle\Model\TicketFlags $service */
        $service = $this->get('data.ticketflags');
        $tickets = $service->getAllTicketsForFlag($this->getUser()->getId(), $star);

        return View::create(
            $this->dataSerialize($tickets),
            Response::HTTP_OK
        );
    }
}
