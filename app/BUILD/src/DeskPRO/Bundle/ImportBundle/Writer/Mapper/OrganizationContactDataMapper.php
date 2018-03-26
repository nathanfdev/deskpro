<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\OrganizationContactData;

/**
 * Class OrganizationContactDataMapper.
 */
class OrganizationContactDataMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return OrganizationContactData::class;
    }
}
