<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\ImportBundle\Model\TicketCustomDef;

/**
 * Custom def ticket record mapper.
 *
 * Class CustomDefTicket
 */
class CustomDefTicketMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefTicket::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return TicketCustomDef::class;
    }
}
