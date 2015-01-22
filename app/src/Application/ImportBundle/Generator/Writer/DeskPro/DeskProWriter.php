<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Writer\DeskPro;

use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\GeneratorConfigAwareInterface;
use Application\ImportBundle\Generator\LoggerAwareInterface;
use Application\ImportBundle\Generator\ProgressBarAwareInterface;
use Application\ImportBundle\Generator\Writer\AbstractWriter;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Generator deskpro writer
 * Imports entities into deskpro database
 *
 * Class DeskProWriter
 * @package Application\ImportBundle\Generator\Writer\DeskPro
 */
class DeskProWriter extends AbstractWriter
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
     * Constructor
     *
     * @param Importer\Collection $importers
     * @param ObjectManager       $entity_manager
     */
    public function __construct(Importer\Collection $importers, ObjectManager $entity_manager)
    {
        $this->importers      = $importers;
        $this->entity_manager = $entity_manager;
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
    public function writeData(EntityInterface $entity)
    {
        $records = $this->getImporter($entity)->getDoctrineEntities($entity);
        foreach ($records as $record) {
            $this->entity_manager->persist($record);
        }

        $this->entity_manager->flush();

        return true;
    }

    /**
     * Returns importer by exported entity
     *
     * @param EntityInterface $entity
     *
     * @return Importer\ImporterInterface
     * @throws \Exception
     */
    private function getImporter(EntityInterface $entity)
    {
        foreach ($this->importers as $importer) {
            /** @var Importer\ImporterInterface $importer */
            if ($entity->getType() === $importer->getEntityType()) {
                if ($this->config && $importer instanceof GeneratorConfigAwareInterface) {
                    /** @var GeneratorConfigAwareInterface $importer */
                    $importer->setConfig($this->config);
                }
                if ($this->logger && $importer instanceof LoggerAwareInterface) {
                    /** @var LoggerAwareInterface $importer */
                    $importer->setLogger($this->logger);
                }
                if ($this->progress_bar && $importer instanceof ProgressBarAwareInterface) {
                    /** @var ProgressBarAwareInterface $importer */
                    $importer->setProgressBarHelper($this->progress_bar);
                }

                return $importer;
            }
        }

        throw new \Exception(sprintf('Entity `%s` not supported', get_class($entity)));
    }
}
