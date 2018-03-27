<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model;

/**
 * DeskPRO importer interface.
 *
 * Interface ImporterInterface
 */
interface EntityHandlerInterface
{
    /**
     * Referred entity type.
     *
     * @return string
     */
    public static function getModelClass();

    /**
     * Parses to a collection of the importing DeskPRO doctrine entities.
     *
     * @param Model\PrimaryImportModelInterface $model
     * @param string                            $brandName
     *
     * @throws \Exception
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null);
}
