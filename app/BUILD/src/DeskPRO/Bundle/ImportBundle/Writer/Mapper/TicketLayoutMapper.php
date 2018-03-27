<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\TicketLayout;

/**
 * Ticket layout record mapper.
 *
 * Class TicketLayout
 */
class TicketLayoutMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return TicketLayout::class;
    }

    /**
     * Returns all ticket layouts.
     *
     * @return TicketLayout[]
     */
    public function findAll()
    {
        return $this->em->getRepository($this->getEntityClass())->findAll();
    }
}
