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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count as CountModel;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketStarType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides API access to the ticket stars.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_stars")
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
     * @Rest\Get("")
     */
    public function listAction()
    {
        return View::create($this->wrap($this->getTicketStars()));
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
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     */
    public function putAction($id, Request $request)
    {
        if (!isset(TicketFlagged::$colorMap[$id])) {
            throw $this->createNotFoundException();
        }

        $color = TicketFlagged::$colorMap[$id];
        $model = $this->get('data.ticket_stars')->findOrCreateStarNamePersonPref($this->getUser(), $color);

        $form = $this->createForm(TicketStarType::class, $model);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->getManager()->persist($model);
        $this->getManager()->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
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
     * @Rest\Get("/counts")
     */
    public function getTicketStarsCountsAction()
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketFlagged $repository */
        $repository    = $this->getManager()->getRepository(TicketFlagged::class);
        $countsMapping = [];

        foreach ($repository->getCountsForPerson($this->getUser()) as $color => $starCount) {
            $countsMapping[$color] = $starCount;
        }

        $count = CountModel::create(0, null, null, null, 'ticket_star');
        foreach ($this->getTicketStars() as $star) {
            $starCount = isset($countsMapping[$star->getColor()]) ? $countsMapping[$star->getColor()] : 0;
            $count->addNested($starCount, $star->getId(), 'ticket_star', $star->getName(), true);
        }

        return View::create($this->wrap($count));
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
     * @Rest\Get("/{star}/tickets")
     */
    public function getTicketsAction(Request $request, $star)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, ['star' => $star]);
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketStar[]
     */
    protected function getTicketStars()
    {
        return $this->get('data.ticket_stars')->getTicketStars($this->getUser());
    }
}
