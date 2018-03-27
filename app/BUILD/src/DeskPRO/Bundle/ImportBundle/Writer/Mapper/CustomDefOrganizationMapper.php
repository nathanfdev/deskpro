<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\CustomDefOrganization;
use DeskPRO\Bundle\ImportBundle\Model\OrganizationCustomDef;

/**
 * Custom def organization record mapper.
 *
 * Class CustomDefOrganization
 */
class CustomDefOrganizationMapper extends AbstractContainerMapper implements CustomDefMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return CustomDefOrganization::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return OrganizationCustomDef::class;
    }
}
