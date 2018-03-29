<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Interface ImportMapKeyAwareInterface.
 */
interface ImportMapKeyAwareInterface
{
    /**
     * @return string
     */
    public static function getImportMapKey();
}
