<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use DeskPRO\Bundle\ImportBundle\Model\OidAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CreateEntityHelper.
 */
class CreateEntityHelper
{
    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ImportMapMapper $importMapMapper
     * @param LoggerInterface $logger
     */
    public function __construct(ImportMapMapper $importMapMapper, LoggerInterface $logger)
    {
        $this->importMapMapper = $importMapMapper;
        $this->logger          = $logger;
    }

    /**
     * @param MapperInterface        $mapper
     * @param OidAwareModelInterface $model
     *
     * @return object
     */
    public function findOrCreateEntity(MapperInterface $mapper, OidAwareModelInterface $model)
    {
        $entity = null;
        if ($model->getOid()) {
            $entityId = $this->importMapMapper->findIdByModel($model);
            if ($entityId) {
                $entity = $mapper->find($entityId);
                if ($entity) {
                    $this->logger->debug("Found existing {$mapper->getEntityClass()} `{$entity->getId()}`");
                }
            }
        }
        if (!$entity) {
            $this->logger->debug("Creating new {$mapper->getEntityClass()} `{$model->getOid()}`");

            $entityClass = $mapper->getEntityClass();
            $entity      = new $entityClass();
        }

        return $entity;
    }
}
