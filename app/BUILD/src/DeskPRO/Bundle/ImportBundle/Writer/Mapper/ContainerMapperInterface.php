<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Interface ContainerMapperInterface.
 */
interface ContainerMapperInterface extends MapperInterface
{
    /**
     * @return string
     */
    public static function getMapperEntityClass();
}
