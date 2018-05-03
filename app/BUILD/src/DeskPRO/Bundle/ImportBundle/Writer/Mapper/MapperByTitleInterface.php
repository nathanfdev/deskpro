<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Mapper interface to find existing DeskPRO records by title.
 *
 * Interface MapperByTitleInterface
 */
interface MapperByTitleInterface
{
    /**
     * Returns the DeskPRO record by title.
     *
     * @param string $title
     *
     * @return mixed
     */
    public function findOneByTitle($title);
}
