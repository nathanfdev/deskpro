<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

/**
 * This is a representation of a data transfer object which holds raw values for a search asset filters.
 * It is used to build a SearchAssetFilter.
 */
interface SearchAssetFilterOptions
{
    /**
     * @return string
     */
    public function getFilePathPattern();

    /**
     * @return string
     */
    public function getFileExtension();
}
