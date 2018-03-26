<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\Indexer;

/**
 * A document represents something that we'll insert into the index.
 */
interface DocumentInterface
{
    /**
     * Get the unique ID for this document in the index.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get the type of document.
     *
     * @return string
     */
    public function getContentTypeName();

    /**
     * Get the data to index.
     *
     * @return array
     */
    public function getData();
}
