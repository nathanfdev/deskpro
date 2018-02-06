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

namespace DeskPRO\Bundle\ImportBundle\Writer;

use Application\DeskPRO\Search\EntityWatcher\EntityWatcher;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;

/**
 * Imports entities into the DeskPRO database.
 */
class ModelWriter
{
    /**
     * @var EntityHandlerRegistry
     */
    private $entityHandlers;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EntityWatcher
     */
    private $entityWatcher;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AppEnvInterface
     */
    private $appEnv;

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
     * @param EntityHandlerRegistry $entityHandlers
     * @param EntityManager         $em
     * @param EntityWatcher         $entityWatcher
     * @param Serializer            $serializer
     * @param AppEnvInterface       $appEnv
     * @param ImportMapMapper       $importMapMapper
     * @param LoggerInterface       $logger
     */
    public function __construct(
        EntityHandlerRegistry $entityHandlers,
        EntityManager         $em,
        EntityWatcher         $entityWatcher,
        Serializer            $serializer,
        AppEnvInterface       $appEnv,
        ImportMapMapper       $importMapMapper,
        LoggerInterface       $logger
    ) {
        $this->entityHandlers  = $entityHandlers;
        $this->em              = $em;
        $this->entityWatcher   = $entityWatcher;
        $this->serializer      = $serializer;
        $this->appEnv          = $appEnv;
        $this->importMapMapper = $importMapMapper;
        $this->logger          = $logger;
    }

    /**
     * @param PrimaryImportModelInterface $model
     * @param string                      $brandName
     *
     * @throws \Exception
     */
    public function writeModel(PrimaryImportModelInterface $model, $brandName = null)
    {
        $this->em->beginTransaction();
        $this->appEnv->setRuntimeVar('dp.is_importing', true);

        try {
            $this->importMapMapper->setOidPrefix($model->getOidPrefix());

            $handler = $this->entityHandlers->getHandler($model);
            $handler->writeModel($model, $brandName);

            $this->em->flush();
            $this->entityWatcher->flushUpdatesQuiet();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Unable to create `%s` with oid `%s`. Reason %s',
                get_class($model), $model->getOid(), $e->__toString()
            ));
            $this->logger->error($this->serializer->serialize($model, 'json'));

            // Entity manager could become closed if some sql error occurred
            // so no need to keep writing, break the process
            if (!$this->em->isOpen()) {
                throw $e;
            }

            $this->em->rollback();
        } finally {
            $this->em->clear();
            $this->appEnv->unsetRuntimeVar('dp.is_importing');
            $this->importMapMapper->setOidPrefix('');
        }
    }
}
