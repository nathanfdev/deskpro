<?php

/**
 * DeskPRO.
 *
 * @category Search
 */

namespace Application\DeskPRO\Search\SearcherResult;

/**
 * Search adapter.
 */
interface ResultInterface
{
    /**
     * Get the result ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get the type of result this is.
     *
     * @return string
     */
    public function getContentTypeName();

    /**
     * Get all result data, generally used with transformers to fetch a real object.
     *
     * @return array
     */
    public function getData();

    /**
     * Get a preview that highlights the search term, or null if there is no highlight.
     * (Either unspoorted, or the kind of search doesn't have a highlight).
     *
     * @return string|null
     */
    public function getHighlight();
}
