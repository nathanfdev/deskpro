<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\TicketCategory;

/**
 * Class TicketCategoryMapper.
 */
class TicketCategoryMapper extends AbstractCategoryMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return TicketCategory::class;
    }
}
