<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Ticket;

/**
 * Ticket record mapper.
 *
 * Class Ticket
 */
class TicketMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Ticket::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        /** @var Ticket $entity */
        $entity = parent::findOneBy($criteria);

        if ($entity) {
            $entity->disableAutoTicketProcess();
            $entity->__dp_skip_ticket_manager = true;
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        /** @var Ticket $entity */
        $entity = parent::find($id);

        if ($entity) {
            $entity->disableAutoTicketProcess();
            $entity->__dp_skip_ticket_manager = true;
        }

        return $entity;
    }
}
