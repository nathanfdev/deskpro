<?php

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
