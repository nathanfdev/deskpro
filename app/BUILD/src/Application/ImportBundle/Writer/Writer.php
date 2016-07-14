<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Writer;

use Application\DeskPRO\Search\EntityWatcher\EntityWatcher;
use Application\ImportBundle\Importer\ImporterContext;
use Application\ImportBundle\Model\ImportModelInterface;
use Application\ImportBundle\Writer\EntityHandler\EntityHandlerInterface;
use Application\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use Application\ImportBundle\Writer\Mapper\ImportMapMapper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Orb\Util\Util;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * DeskPRO generator writer.
 * Imports entities into the DeskPRO database.
 *
 * Class DeskProWriter
 */
class Writer implements WriterInterface
{
    /**
     * @var EntityHandlerRegistry
     */
    private $entityHandlers;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var ImportMapMapper
     */
    private $oidMapper;

    /**
     * @var EntityWatcher
     */
    private $entityWatcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityHandlerRegistry $entityHandlers
     * @param EntityManager         $em
     * @param ImportMapMapper       $oidMapper
     * @param EntityWatcher         $entityWatcher
     * @param ValidatorInterface    $validator
     * @param LoggerInterface       $logger
     */
    public function __construct(
        EntityHandlerRegistry    $entityHandlers,
        EntityManager            $em,
        ImportMapMapper          $oidMapper,
        EntityWatcher            $entityWatcher,
        ValidatorInterface       $validator,
        LoggerInterface          $logger
    ) {
        $this->entityHandlers = $entityHandlers;
        $this->em             = $em;
        $this->oidMapper      = $oidMapper;
        $this->entityWatcher  = $entityWatcher;
        $this->validator      = $validator;
        $this->logger         = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function writeData(ImporterContext $context, ImportModelInterface $model)
    {
        $handlers = $this->entityHandlers->getHandlers($model);
        foreach ($handlers as $handler) {
            try {
                $entityId = null;
                $entityId = $this->oidMapper->findRefByOldId($model->getImportMapKey(), $model->getOid());

                if ($entityId) {
                    $this->logger->debug(sprintf(
                        'Found existing mapping for `%s`, oid = %s, id = %d',
                        $model->getImportMapKey(), $model->getOid(), $entityId
                    ));
                }

                /* @var EntityHandlerInterface $handler */
                $handler->reset()->prepare($model, $entityId);
                $entities = $handler->getDoctrineEntities();

                // validate entities
                foreach ($entities->getPersistEntities() as $entity) {
                    $errors = $this->validator->validate($entity);
                    if (count($errors)) {
                        $this->logger->alert($errors);
                        continue;
                    }
                }

                // persist entities
                foreach ($entities->getPersistEntities() as $entity) {
                    if (!$context->isDryRun()) {
                        $this->em->persist($entity);
                    }
                }

                $this->em->flush();

                foreach ($entities->getPersistEntities() as $entity) {
                    $this->logger->debug(sprintf(
                        'Persisted %s #%s',

                        Util::getBaseClassname($entity),
                        method_exists($entity, 'getId') ? $entity->getId() : '_'
                    ));
                }

                // Save primary entity oid mapping
                $primaryEntity = $entities->getPrimaryEntity();
                if ($primaryEntity && method_exists($primaryEntity, 'getId') && null === $entityId) {
                    $this->oidMapper->saveMapping($model->getImportMapKey(), $model->getOid(), $primaryEntity->getId());
                }

                // Save related entity mapping
                foreach ($entities->getImportMapEntities() as $oidMap) {
                    $mapEntity = $oidMap->getEntity();

                    if (!$this->oidMapper->findRefByOldId($mapEntity->getImportMapKey(), $mapEntity->getOid())) {
                        $importMap = $oidMap->createDoctrineImportMapEntity();

                        $this->em->persist($importMap);
                        $this->logger->info(sprintf(
                            'Persisted a new import map %s, oid=%s, id=%s',
                            $importMap->getTypename(), $importMap->getOldId(), $importMap->getNewId()
                        ));
                    } else {
                        $this->logger->warning(sprintf(
                            'Unable to add a new import map %s, oid=%s, already exists',
                            $mapEntity->getImportMapKey(), $mapEntity->getOid()
                        ));
                    }
                }

                $this->em->flush();
                $this->em->clear();
                $this->entityWatcher->flushUpdatesQuiet();
            } catch (Mapper\MapperException $e) {
                $this->logger->warning(sprintf(
                    'Unable to create `%s` with oid `%s`. Reason %s',
                    get_class($model), $model->getOid(), $e->__toString()
                ));
            }
        }
    }
}
