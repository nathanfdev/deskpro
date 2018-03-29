<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\PhoneNumber;

/**
 * Class PhoneNumberMapper.
 */
class PhoneNumberMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return PhoneNumber::class;
    }
}
