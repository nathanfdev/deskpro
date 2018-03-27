<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Interface CustomDefMapperInterface.
 */
interface CustomDefMapperInterface extends MapperInterface
{
    /**
     * @return string
     */
    public static function getModelClass();
}
