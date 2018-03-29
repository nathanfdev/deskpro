<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Class AbstractContainerMapper.
 */
abstract class AbstractContainerMapper extends AbstractEntityManagerMapper implements ContainerMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return static::getMapperEntityClass();
    }
}
