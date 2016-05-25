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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\Participants;

use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants\TicketParticipantType;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractTicketParticipantsController.
 */
abstract class AbstractTicketParticipantsController extends CrudSubController
{
    use TicketSaveTrait, TicketAwarePersistModelTrait;

    public static $entity         = TicketParticipant::class;
    public static $type           = TicketParticipantType::class;
    public static $parentProperty = 'ticket';
    public static $exposeOnly     = ['get', 'list', 'post', 'delete'];

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        $qb
            ->join("$alias.person", 'p')
            ->andWhere('p.is_agent = :is_agent')
            ->setParameter('is_agent', $this->isAgent())
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketParticipant $model
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'owner'    => $this->findParentOr404(),
            'is_agent' => $this->isAgent(),
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
            ->andWhere('p.is_agent = :is_agent')
            ->andWhere('p.id = :person_id')
            ->andWhere('t.id = :ticket_id')
            ->setParameter('is_agent', $this->isAgent())
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

    /**
     * @return bool
     */
    abstract protected function isAgent();
}
