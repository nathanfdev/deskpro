<?php

namespace DeskPRO\Bundle\ImportBundle\Storage;

use DeskPRO\Bundle\AppBundle\Form\Error\ValidatorErrorsGenerator;
use DeskPRO\Bundle\ImportBundle\Model\BatchConfig;
use DeskPRO\Bundle\ImportBundle\Model\PrimaryImportModelInterface;
use DeskPRO\Bundle\ImportBundle\Parser\Parser;
use DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Parser from json files.
 */
class Storage
{
    /**
     * @var EntityHandlerRegistry
     */
    private $entityHandlerRegistry;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var StorageAdapterInterface
     */
    private $storageAdapter;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ValidatorErrorsGenerator
     */
    private $errorsGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityHandlerRegistry    $entityHandlerRegistry
     * @param StorageAdapterInterface  $storageAdapter
     * @param Serializer               $serializer
     * @param Parser                   $parser
     * @param ValidatorInterface       $validator
     * @param ValidatorErrorsGenerator $errorsGenerator
     * @param LoggerInterface          $logger
     */
    public function __construct(
        EntityHandlerRegistry    $entityHandlerRegistry,
        StorageAdapterInterface  $storageAdapter,
        Serializer               $serializer,
        Parser                   $parser,
        ValidatorInterface       $validator,
        ValidatorErrorsGenerator $errorsGenerator,
        LoggerInterface          $logger
    ) {
        $this->entityHandlerRegistry = $entityHandlerRegistry;
        $this->storageAdapter        = $storageAdapter;
        $this->serializer            = $serializer;
        $this->parser                = $parser;
        $this->validator             = $validator;
        $this->errorsGenerator       = $errorsGenerator;
        $this->logger                = $logger;
    }

    /**
     * @param string $modelClass
     *
     * @return int
     */
    public function getNextBatchId($modelClass)
    {
        return $this->storageAdapter->getNextBatchId($this->entityHandlerRegistry->getTypeByModelClass($modelClass));
    }

    /**
     * @param string $modelClass
     * @param int    $batchId
     *
     * @return bool
     */
    public function hasBatch($modelClass, $batchId)
    {
        return $this->storageAdapter->hasBatch($this->entityHandlerRegistry->getTypeByModelClass($modelClass), $batchId);
    }

    /**
     * Returns a collection of records of the current type.
     *
     * @param string $modelClass
     * @param int    $batchId
     *
     * @return PrimaryImportModelInterface[]
     */
    public function readBatch($modelClass, $batchId)
    {
        $type = $this->entityHandlerRegistry->getTypeByModelClass($modelClass);
        $data = $this->storageAdapter->readBatch($type, $batchId);

        $models = [];
        foreach ($data as $oid => $rawData) {
            $model = $this->parser->exportRawData($oid, $rawData, $modelClass);
            if ($model) {
                $models[$oid] = $model;
            }
        }

        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function readModel($modelClass, $batchId, $oid)
    {
        $type = $this->entityHandlerRegistry->getTypeByModelClass($modelClass);

        return $this->storageAdapter->readModel($type, $batchId, $oid.'.json');
    }

    /**
     * @param PrimaryImportModelInterface $model
     * @param int                         $batchNum
     *
     * @throws \Exception
     */
    public function writeModel(PrimaryImportModelInterface $model, $batchNum)
    {
        if (!$model->getOid()) {
            throw new \RuntimeException('Model has no oid');
        }

        // validate model
        $errors = $this->validator->validate($model);
        if (count($errors)) {
            $formattedErrors = $this->errorsGenerator->generateValidatorErrors($errors);
            $encodedErrors   = $this->serializer->serialize($formattedErrors, 'json');

            throw new \RuntimeException(sprintf(
                "%s #%s validation is failed:\n%s",
                get_class($model), $model->getOid(), $encodedErrors
            ));
        }

        // write model
        $type    = $this->entityHandlerRegistry->getTypeByModelClass(get_class($model));
        $encoded = $this->serializer->serialize($model, 'json');

        $this->storageAdapter->writeModel($type, $batchNum, $model->getOid().'.json', $encoded);
    }

    /**
     * @return BatchConfig
     */
    public function readBatchConfig()
    {
        $encodedConfig = $this->storageAdapter->readBatchConfig();
        if ($encodedConfig) {
            return $this->serializer->deserialize($encodedConfig, BatchConfig::class, 'json');
        }

        return new BatchConfig();
    }

    /**
     * @param BatchConfig $config
     */
    public function writeBatchConfig(BatchConfig $config)
    {
        $encodedConfig = $this->serializer->serialize($config, 'json');
        $this->storageAdapter->writeBatchConfig($encodedConfig);
    }

    /**
     * @param string $data
     */
    public function writeLogFile($data)
    {
        // get last log file to append data
        $logMaxSize = 10 * 1024 * 1024; // 10mb
        if ($filename = $this->storageAdapter->getLastLogFile($logMaxSize)) {
            $data = $this->storageAdapter->readLogFile($filename).$data;
        } else {
            $date     = new \DateTime();
            $filename = 'import_log.'.$date->format('c').'.log';
        }

        $this->storageAdapter->writeLogFile($filename, $data);
    }
}
