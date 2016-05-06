<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count as CountModel;
use DeskPRO\Bundle\AppBundle\Entity\TicketStar;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\TaskStarType;
use DeskPRO\Bundle\AppBundle\Model\TicketStars;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketStar as TicketStarModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API access to the ticket stars.
 *
 * @ApiModes("all")
 */
class TicketStarsController extends BaseController
{
    /**
     * Get a list of ticket stars.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     description="get a list of ticket stars",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketStar>")
     * )
     *
     * @Rest\Get("/ticket_stars", name="api_ticket_stars")
     */
    public function listAction()
    {
        /** @var TicketStars $service */
        $service     = $this->get('data.ticket_stars');
        $customNames = $service->getCustomNames($this->getUser());

        $stars = [];
        for ($i = 1; $i <= 7; ++$i) {
            $name    = array_key_exists($i, $customNames) ? $customNames[$i] : TicketStar::idToColorLabel($i);
            $stars[] = new TicketStarModel($i, $name, TicketStar::idToColorHex($i));
        }

        return View::create($this->wrap($stars), Response::HTTP_OK);
    }

    /**
     * Update a ticket star name.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     description="Update ticket star name",
     *     statusCodes={
     *         204="If update was successful",
     *         400="If you provided wrong formed request",
     *     },
     *     requirements={
     *         {"name" = "id", "requirement" = "\d+", "dataType" = "integer", "description" = "the id of star to update"},
     *         {"name" = "name", "requirement" = "\w", "dataType" = "string", "description" = "the name of star to set"},
     *     }
     * )
     *
     * @param int     $id
     * @param Request $request
     *
     * @return Response
     *
     * @Rest\Put("/ticket_stars/{id}", requirements={"id"="\d+"})
     */
    public function putAction($id, Request $request)
    {
        /** @var TicketStars $service */
        $service = $this->get('data.ticket_stars');
        $person  = $this->getUser();

        $content = json_decode($request->getContent(), true);
        if (!array_key_exists('name', $content) || !$content['name']) {
            $service->removeStarNamePersonSetting($person, $id);

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $model = $service->findOrCreateStarNamePersonSetting($person, $id);
        $form  = $this->createForm(new TaskStarType(), $model);
        $form->submit($content, true);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($model);
            $em->flush();

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        throw new InvalidFormException($form);
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get the counts of tickets marked with each star",
     *     statusCodes={
     *         200="Returned if success"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @Rest\Get("/ticket_stars_counts", name="api_ticket_star_all_counts")
     */
    public function getTicketStarsCountsAction()
    {
        /** @var TicketStars $service */
        $service     = $this->get('data.ticket_stars');
        $customNames = $service->getCustomNames($this->getUser());

        $count = CountModel::create(0, null, null, null, 'ticket_star');
        foreach ($service->getStars() as $i => $color) {
            $starId = $i + 1;
            $name   = array_key_exists($starId, $customNames) ?
                $customNames[$starId] : TicketStar::idToColorLabel($starId);
            $records   = $service->getAllRecordsForStar($this->getUser()->getId(), $starId);
            $starCount = count($records);
            $count->addNested($starCount, $starId, 'ticket_star', $name, true);
        }

        return View::create($this->wrap($count), Response::HTTP_OK);
    }

    /**
     * Get a count of tickets that current person flagged with provided star.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get the count of tickets marked with given star",
     *     requirements={
     *         {"name" = "star", "requirement" = "\d+", "dataType" = "integer", "description" = "the id of star to filter"},
     *     },
     *     statusCodes={
     *         200="Returned if success"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @param int $star
     *
     * @return View
     *
     * @Rest\Get("/ticket_stars/{star}/count", name="api_ticket_star_count")
     */
    public function getTicketStarCountAction($star)
    {
        $tickets = $this->get('data.ticket_stars')->getAllRecordsForStar($this->getUser()->getId(), $star);

        $count = count($tickets);

        return View::create($this->wrap(CountModel::fromValue($count)), Response::HTTP_OK);
    }

    /**
     * Fetch a list of tickets starred with provided star.
     *
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get the tickets for a star",
     *     statusCodes={
     *         200="Returned if success"
     *     },
     *     requirements={
     *         {"name" = "star", "requirement" = "\d+", "dataType" = "integer", "description" = "the id of star to filter by"},
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     *
     * @param Request $request
     * @param int     $star
     *
     * @return View
     *
     * @Rest\Get("/ticket_stars/{star}/tickets", name="api_ticket_star_tickets")
     */
    public function getTicketsAction(Request $request, $star)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, ['star' => $star]);
    }
}
