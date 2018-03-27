<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO person custom def importer.
 *
 * Class PersonCustomDef
 */
class PersonCustomDefHandler extends AbstractCustomDefHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\PersonCustomDef::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDefMapper()
    {
        return $this->mappers->getPersonCustomDefMapper();
    }
}
