<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use DeskPRO\Bundle\ImportBundle\Model\OidAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\HelperRegistry;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperRegistry;
use Psr\Log\LoggerInterface;

/**
 * Abstract DeskPRO importer
 * Finds or creates DeskPRO entities.
 *
 * Class AbstractImporter
 */
abstract class AbstractEntityHandler implements EntityHandlerInterface
{
    /**
     * @var MapperRegistry
     */
    protected $mappers;

    /**
     * @var HelperRegistry
     */
    protected $helpers;

    /**
     * @var EntityPersister
     */
    protected $persister;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param HelperRegistry  $helpers
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(MapperRegistry $mappers, HelperRegistry $helpers, EntityPersister $persister, LoggerInterface $logger)
    {
        $this->mappers   = $mappers;
        $this->helpers   = $helpers;
        $this->persister = $persister;
        $this->logger    = $logger;
    }

    /**
     * @param MapperInterface        $mapper
     * @param OidAwareModelInterface $model
     *
     * @return object
     */
    public function findOrCreateEntity(MapperInterface $mapper, OidAwareModelInterface $model)
    {
        return $this->helpers->getCreateEntityHelper()->findOrCreateEntity($mapper, $model);
    }
}
