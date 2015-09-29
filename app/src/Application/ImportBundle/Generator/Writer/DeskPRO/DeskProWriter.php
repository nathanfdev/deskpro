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
     * @var EntityWatcher
     */
    private $entity_watcher;

    /**
     * Constructor.
     *
     * @param Importer\Collection $importers
     * @param ObjectManager       $entity_manager
     * @param EntityWatcher       $entity_watcher
     */
    public function __construct(Importer\Collection $importers, ObjectManager $entity_manager, EntityWatcher $entity_watcher)
    {
        $this->importers      = $importers;
        $this->entity_manager = $entity_manager;
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
                if ($importer instanceof SkipDuplicateInterface) {
                    $importer->checkAlreadyExists($entity);
                }

                /* @var ImporterInterface $importer */
                $records = $importer->getDoctrineEntities($entity);
                foreach ($records as $record) {
                    if ($this->config->isDryRun() === false) {
                        $this->entity_manager->persist($record);
                    }
                }

                $this->entity_manager->flush();
                $this->entity_manager->clear();
                $this->entity_watcher->flushUpdatesQuiet();

                foreach ($records as $r) {
                    $this->logDebug(sprintf('Persisted %s #%s', Util::getBaseClassname($r), method_exists($r, 'getId') ? $r->getId() : '_'));
                }
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
     * @throws \Exception
     * @return Importer\Collection
     *
     */
    private function getImporters(EntityInterface $entity)
    {
        $importers = $this->importers->getByEntityType($entity->getType());
        foreach ($importers as $importer) {
            if ($this->config && $importer instanceof GeneratorConfigAwareInterface) {
                /* @var GeneratorConfigAwareInterface $importer */
                $importer->setConfig($this->config);
            }
            if ($this->logger && $importer instanceof LoggerAwareInterface) {
                /* @var LoggerAwareInterface $importer */
                $importer->setLogger($this->logger);
            }
            if ($this->progress_bar && $importer instanceof ProgressBarAwareInterface) {
                /* @var ProgressBarAwareInterface $importer */
                $importer->setProgressBarHelper($this->progress_bar);
            }
        }

        return $importers;
    }
}
