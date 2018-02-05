<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFollowUpType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketFollowUpsController.
 *
 * @ApiModes("session")
 * @Rest\Route("/tickets/{parentId}/follow-ups")
 * @Feature("follow_up")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketFollowUpType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp",
 *          "ticket"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TicketFollowUpsController extends AbstractTicketsCrudSubController
{
    use TicketSaveTrait, TicketAwarePersistModelTrait;

    public static $entity         = TicketFollowUp::class;
    public static $type           = TicketFollowUpType::class;
    public static $parentProperty = 'ticket';

    /**
     * @Rest\Post("/{id}/cancel")
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function cancelAction($id, Request $request)
    {
        $entity = $this->findEntity($id, $request);
        if ($entity->getStatus() === TicketFollowUp::STATUS_DONE) {
            throw $this->createBadRequestException('The follow up is already done');
        }

        $entity->setStatus(TicketFollowUp::STATUS_CANCELLED);

        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket' => $this->findParentOr404(),
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketFollowUp $entity
     */
    protected function deleteEntity($entity)
    {
        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeFollowUp($entity);

        $this->saveTicket($ticket);
    }
}
