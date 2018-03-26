<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\PersonContactData;

/**
 * Class PersonContactDataMapper.
 */
class PersonContactDataMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return PersonContactData::class;
    }
}
