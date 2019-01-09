<?php

namespace DeskPRO\Bundle\ImportBundle\Writer;

use Application\DeskPRO\Search\EntityWatcher\EntityWatcher;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
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
