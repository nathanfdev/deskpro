<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\CountBadge\Count as CountModel;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketStarNameType;
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
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketStarNameType"
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

        $form = $this->createForm(TicketStarNameType::class, $model);
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
