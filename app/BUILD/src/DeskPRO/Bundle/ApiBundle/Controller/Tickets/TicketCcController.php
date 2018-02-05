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

use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketСсController.
 *
 * @ApiModes("all")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
 * @Rest\Route("/tickets/{parentId}/cc")
 * @ApiDoc(
 *     target="postAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TicketParticipant",
 *          "owner"="Application\DeskPRO\Entity\Ticket",
 *          "agent_interface"=true
 *      }
 *     }
 * )
 */
class TicketCcController extends AbstractTicketsCrudSubController
{
    use TicketSaveTrait, TicketAwarePersistModelTrait;

    public static $entity         = TicketParticipant::class;
    public static $type           = TicketParticipantType::class;
    public static $parentProperty = 'ticket';
    public static $exposeOnly     = ['get', 'list', 'post', 'delete'];

    /**
     * {@inheritdoc}
     *
     * @param TicketParticipant $model
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'owner'           => $this->findParentOr404(),
            'agent_interface' => true,
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('e')
            ->from(self::$entity, 'e')
            ->join('e.person', 'p')
            ->join('e.ticket', 't')
            ->andWhere('p.id = :person_id')
            ->andWhere('t.id = :ticket_id')
            ->setParameter('person_id', $id)
            ->setParameter('ticket_id', $this->findParentOr404()->getId())
        ;

        $entity = $qb->getQuery()->getOneOrNullResult();
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketParticipant $entity
     */
    protected function deleteEntity($entity)
    {
        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeParticipantPerson($entity->getPerson());

        $this->saveTicket($ticket);
    }
}
