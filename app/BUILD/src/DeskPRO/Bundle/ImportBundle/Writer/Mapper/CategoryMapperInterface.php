<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Interface CategoryMapperInterface.
 */
interface CategoryMapperInterface extends MapperInterface
{
    /**
     * @param string $title
     * @param null   $parentId
     *
     * @return mixed
     */
    public function findOneByTitle($title, $parentId = null);

    /**
     * @return mixed
     */
    public function getDefaultCategory();
}
