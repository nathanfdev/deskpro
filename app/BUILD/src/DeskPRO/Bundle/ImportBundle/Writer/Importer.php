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

use DeskPRO\Bundle\ImportBundle\Model\BatchConfig;
use DeskPRO\Bundle\ImportBundle\Model\BatcnPointer;
use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use DeskPRO\Bundle\ImportBundle\Storage\Storage;
use DeskPRO\Bundle\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use DpSys\LowError\SystemErrorHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class Importer.
 */
class Importer
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ModelWriter
     */
    private $modelWriter;

    /**
     * @var EntityHandlerRegistry
     */
    private $entityHandlerRegistry;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param Storage                  $storage
     * @param ValidatorInterface       $validator
     * @param ModelWriter              $modelWriter
     * @param EntityHandlerRegistry    $entityHandlerRegistry
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        Storage                  $storage,
        ValidatorInterface       $validator,
        ModelWriter              $modelWriter,
        EntityHandlerRegistry    $entityHandlerRegistry,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface          $logger
    ) {
        $this->storage               = $storage;
        $this->modelWriter           = $modelWriter;
        $this->validator             = $validator;
        $this->entityHandlerRegistry = $entityHandlerRegistry;
        $this->eventDispatcher       = $eventDispatcher;
        $this->logger                = $logger;
    }

    /**
     * @param BatchConfig $config
     *
     * @return bool
     */
    public function hasRemaining(BatchConfig $config)
    {
        return (bool) $this->getBatchPointer($config);
    }

    /**
     * @param BatcnPointer $pointer
     *
     * @return ArrayCollection|PrimaryImportModelInterface[]
     */
    public function getImportData(BatcnPointer $pointer)
    {
        $collection = new ArrayCollection($this->storage->readBatch(
            $pointer->getModelClass(),
            $pointer->getBatchId())
        );

        foreach ($collection as $model) {
            $errors = $this->validator->validate($model);
            if (count($errors)) {
                // Removing broken entities
                $collection->removeElement($model);
                $this->logger->alert(sprintf(
                    'Validator failure for %s on record #%s: %s',
                    get_class($model), $model->getOid(), $errors
                ));

                if ($model->getRawData()) {
                    foreach (explode("\n", SystemErrorHandler::varToString($model->getRawData(), 2)) as $line) {
                        $this->logger->info($line);
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * @param BatchConfig  $batchConfig
     * @param BatcnPointer $pointer
     * @param bool         $persist
     *
     * @throws \Exception
     */
    public function updateBatchConfig(BatchConfig $batchConfig, BatcnPointer $pointer, $persist = true)
    {
        $type    = $this->entityHandlerRegistry->getTypeByModelClass($pointer->getModelClass());
        $batchId = $pointer->getBatchId();

        $batchConfig->setBatchId($type, $batchId);

        if ($persist) {
            $this->storage->writeBatchConfig($batchConfig);
        }
    }

    /**
     * Writes a collection of entities.
     *
     * @param ArrayCollection $collection
     * @param string          $brandName
     */
    public function writeData(ArrayCollection $collection, $brandName = null)
    {
        foreach ($collection as $model) {
            $this->modelWriter->writeModel($model, $brandName);
        }
    }

    /**
     * @return BatchConfig
     */
    public function readBatchConfig()
    {
        return $this->storage->readBatchConfig();
    }

    /**
     * @param BatchConfig $config
     *
     * @return BatcnPointer
     */
    public function getBatchPointer(BatchConfig $config)
    {
        foreach ($this->entityHandlerRegistry->getModelClasses() as $modelClass) {
            $currentId = $config->getBatchId($this->entityHandlerRegistry->getTypeByModelClass($modelClass));
            if (!$currentId) {
                $nextId = 1;
            } else {
                $nextId = $currentId + 1;
            }

            if ($this->storage->hasBatch($modelClass, $nextId)) {
                return new BatcnPointer($modelClass, $nextId);
            }
        }

        // no data left, break import
        return;
    }
}
