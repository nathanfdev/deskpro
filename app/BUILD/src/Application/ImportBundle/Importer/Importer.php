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

namespace Application\ImportBundle\Importer;

use Application\ImportBundle\Model\BatchConfig;
use Application\ImportBundle\Parser\ParserInterface;
use Application\ImportBundle\Writer\EntityHandler\EntityHandlerRegistry;
use Application\ImportBundle\Writer\WriterInterface;
use DpSys\LowError\SystemErrorHandler;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class Importer.
 */
class Importer
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EntityHandlerRegistry
     */
    private $entityHandlerRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ParserInterface       $parser
     * @param ValidatorInterface    $validator
     * @param WriterInterface       $writer
     * @param Serializer            $serializer
     * @param EntityHandlerRegistry $entityHandlerRegistry
     * @param LoggerInterface       $logger
     */
    public function __construct(
        ParserInterface       $parser,
        ValidatorInterface    $validator,
        WriterInterface       $writer,
        Serializer            $serializer,
        EntityHandlerRegistry $entityHandlerRegistry,
        LoggerInterface       $logger
    ) {
        $this->parser                = $parser;
        $this->writer                = $writer;
        $this->validator             = $validator;
        $this->serializer            = $serializer;
        $this->entityHandlerRegistry = $entityHandlerRegistry;
        $this->logger                = $logger;
    }

    /**
     * Returns a count of records of all types to be exported.
     *
     * @param ImporterContext $context
     *
     * @return int
     */
    public function getTotalCount(ImporterContext $context)
    {
        $count = 0;
        foreach ($this->entityHandlerRegistry->getModelClasses() as $modelClass) {
            $count += $this->parser->getCountByType($context, $modelClass);
        }

        return $count;
    }

    /**
     * @param ImporterContext $context
     *
     * @return ImporterCollection
     */
    public function getImportData(ImporterContext $context)
    {
        $collection = new ImporterCollection();
        foreach ($this->entityHandlerRegistry->getModelClasses() as $type) {
            $this->printHeader("Export `$type` collection");
            $collection->addByType($type, $this->parser->exportByType($context, $type));
        }

        return $collection;
    }

    /**
     * @param ImporterContext $context
     *
     * @return BatchConfig
     */
    public function getNextBatchConfig(ImporterContext $context)
    {
        $nextId = $context->getBatchConfig()->getId() + 1;

        $nextBatchConfig = clone $context->getBatchConfig();
        $nextBatchConfig
            ->setId($nextId)
            ->setDateModified(new \DateTime())
            ->setHasRemaining(is_dir($context->getInputPath().DIRECTORY_SEPARATOR.$nextId))
        ;

        return $nextBatchConfig;
    }

    /**
     * @param ImporterContext $context
     * @param BatchConfig     $batchConfig
     *
     * @throws \Exception
     */
    public function writeBatchConfig(ImporterContext $context, BatchConfig $batchConfig)
    {
        $encodedConfig = $this->serializer->serialize($batchConfig, 'json');

        if (!@file_put_contents($context->getBatchFilePath(), $encodedConfig)) {
            throw new \Exception("Unable to write batch config to {$context->getBatchFilePath()}");
        }
    }

    /**
     * @param ImporterCollection $collection
     *
     * @throws \Exception
     */
    public function validateData(ImporterCollection $collection)
    {
        $col = $collection->toArray();
        foreach ($col as $type => $models) {
            if (!$this->entityHandlerRegistry->hasClass($type)) {
                continue;
            }
            $this->printHeader("Write `$type` collection");
            foreach ($models as $model) {
                $errors = $this->validator->validate($model);
                if (count($errors)) {
                    // Removing broken entities
                    $collection->remove($model);
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
        }
    }

    /**
     * Writes a collection of entities.
     *
     * @param ImporterCollection $collection
     * @param string             $brandName
     * @param bool               $dryRun
     */
    public function writeData(ImporterCollection $collection, $brandName = null, $dryRun = false)
    {
        $col = $collection->toArray();
        foreach ($col as $type => $models) {
            if (!$this->entityHandlerRegistry->hasClass($type)) {
                continue;
            }
            $this->printHeader("Write `$type` collection");
            foreach ($models as $model) {
                $this->writer->writeData($model, $brandName, $dryRun);
            }
        }
    }

    /**
     * @param string $title
     */
    private function printHeader($title)
    {
        $this->logger->info('');
        $this->logger->info('=====================================');
        $this->logger->info($title);
        $this->logger->info('=====================================');
    }
}
