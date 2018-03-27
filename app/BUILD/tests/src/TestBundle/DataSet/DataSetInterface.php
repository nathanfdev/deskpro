<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\DataSet;

/**
 * Interface DataSetInterface.
 */
interface DataSetInterface
{
    /**
     * The ID you call in code to install this dataset.
     *
     * @return string
     */
    public function getId();

    /**
     * Deletes current database and installs this set.
     *
     * @param bool $recreateStructure
     */
    public function install($recreateStructure = false);
}
