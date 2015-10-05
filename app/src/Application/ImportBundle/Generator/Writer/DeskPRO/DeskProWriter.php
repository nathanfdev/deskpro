<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO;

use Application\DeskPRO\Search\EntityWatcher\EntityWatcher;
use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\GeneratorConfigAwareInterface;
use Application\ImportBundle\Generator\LoggerAwareInterface;
use Application\ImportBundle\Generator\ProgressBarAwareInterface;
use Application\ImportBundle\Generator\Writer\AbstractWriter;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\ImporterInterface;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper\OidMapper;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\SkipDuplicateInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Orb\Util\Util;

/**
 * DeskPRO generator writer
 * Imports entities into the DeskPRO database.
 *
 * Class DeskProWriter
 */
final class DeskProWriter extends AbstractWriter
{
    /**
     * @var Importer\Collection
     */
    private $importers;

    /**
     * @var ObjectManager
     */
    private $entity_manager;

    /**
     * @var OidMapper
     */
    private $oid_mapper;

    /**
     * @var EntityWatcher
     */
    private $entity_watcher;

    /**
     * Constructor.
     *
     * @param Importer\Collection $importers
     * @param ObjectManager       $entity_manager
     * @param OidMapper           $oid_mapper
     * @param EntityWatcher       $entity_watcher
     */
    public function __construct(
        Importer\Collection $importers,
        ObjectManager       $entity_manager,
        OidMapper           $oid_mapper,
        EntityWatcher       $entity_watcher
    ) {
        $this->importers      = $importers;
        $this->entity_manager = $entity_manager;
        $this->oid_mapper     = $oid_mapper;
        $this->entity_watcher = $entity_watcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_DESK_PRO;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if (!$this->config->getInputPath()) {
            $this->createOutputDirIfNotExist();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeData(EntityInterface $entity)
    {
        $importers = $this->getImporters($entity);
        foreach ($importers as $importer) {
            try {
                $entity_id = null;
                if ($entity->getImportMapKey()) {
                    $entity_id = $this->oid_mapper->findRefByOldId($entity->getImportMapKey(), $entity->getOid());

                    if ($entity_id) {
                        $this->logDebug(sprintf(
                            'Found existing mapping for `%s`, oid = %s, id = %d',
                            $entity->getImportMapKey(), $entity->getOid(), $entity_id
                        ));
                    }
                }

                if ($importer instanceof SkipDuplicateInterface) {
                    $importer->checkAlreadyExists($entity);
                }

                /* @var ImporterInterface $importer */
                $importer->reset()->prepare($entity, $entity_id);

                $records = $importer->getDoctrineEntities();
                foreach ($records->getPersistEntities() as $record) {
                    if ($this->config->isDryRun() === false) {
                        $this->entity_manager->persist($record);
                    }
                }

                $this->entity_manager->flush();

                foreach ($records->getPersistEntities() as $record) {
                    $this->logDebug(sprintf(
                        'Persisted %s #%s',

                        Util::getBaseClassname($record),
                        method_exists($record, 'getId') ? $record->getId() : '_'
                    ));
                }

                // Save primary entity oid mapping
                if ($entity->getImportMapKey()) {
                    $primary_record = $records->getPrimaryEntity();
                    if ($primary_record && method_exists($primary_record, 'getId') && null === $entity_id) {
                        $this->oid_mapper->saveMapping($entity->getImportMapKey(), $entity->getOid(), $primary_record->getId());
                    }
                }

                // Save related entity mapping
                foreach ($records->getImportMapEntities() as $oid_map) {
                    $map_entity = $oid_map->getEntity();

                    if (!$this->oid_mapper->findRefByOldId($map_entity->getImportMapKey(), $map_entity->getOid())) {
                        $import_map = $oid_map->createDoctrineImportMapEntity();

                        $this->entity_manager->persist($import_map);
                        $this->logInfo(sprintf(
                            'Persisted a new import map %s, oid=%s, id=%s',
                            $import_map->getTypename(), $import_map->getOldId(), $import_map->getNewId()
                        ));
                    } else {
                        $this->logWarning(sprintf(
                            'Unable to add a new import map %s, oid=%s, already exists',
                            $map_entity->getImportMapKey(), $map_entity->getOid()
                        ));
                    }
                }

                $this->entity_manager->flush();
                $this->entity_manager->clear();
                $this->entity_watcher->flushUpdatesQuiet();
            } catch (Importer\Mapper\MapperException $e) {
                $this->logWarning(sprintf(
                    'Unable to create `%s` with oid `%s`. Reason %s',
                    $entity->getType(), $entity->getOid(), $e->__toString()
                ));
            } catch (Importer\DuplicateException $e) {
                $this->logWarning(sprintf(
                    'Duplicate entity `%s` with oid `%s` (Skipping)',
                    $entity->getType(), $entity->getOid()
                ));
            }
        }

        return true;
    }

    /**
     * Returns importer by exported entity.
     *
     * @param EntityInterface $entity
     *
     * @return Importer\Collection
     */
    private function getImporters(EntityInterface $entity)
    {
        $importers = $this->importers->getByEntityType($entity->getType());
        foreach ($importers as $importer) {
            if ($this->config && $importer instanceof GeneratorConfigAwareInterface) {
                $importer->setConfig($this->config);
            }
            if ($this->logger && $importer instanceof LoggerAwareInterface) {
                $importer->setLogger($this->logger);
            }
            if ($this->progress_bar && $importer instanceof ProgressBarAwareInterface) {
                $importer->setProgressBarHelper($this->progress_bar);
            }
        }

        return $importers;
    }
}
