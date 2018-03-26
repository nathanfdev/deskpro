<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefPerson;
use DeskPRO\Bundle\ImportBundle\Model\PersonCustomDef;

/**
 * Custom def people record mapper.
 *
 * Class CustomDefPeople
 */
class CustomDefPersonMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefPerson::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return PersonCustomDef::class;
    }
}
