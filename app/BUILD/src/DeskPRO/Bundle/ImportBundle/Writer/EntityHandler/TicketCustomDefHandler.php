<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO ticket custom def importer.
 *
 * Class TicketCustomDef
 */
class TicketCustomDefHandler extends AbstractCustomDefHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\TicketCustomDef::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDefMapper()
    {
        return $this->mappers->getTicketCustomDefMapper();
    }
}
