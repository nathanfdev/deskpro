<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
