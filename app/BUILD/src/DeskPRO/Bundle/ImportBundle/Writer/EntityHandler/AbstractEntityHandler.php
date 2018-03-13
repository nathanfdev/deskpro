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
