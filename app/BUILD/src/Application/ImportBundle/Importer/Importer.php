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

namespace Application\ImportBundle\Importer;

use Application\ImportBundle\Exporter\ExporterInterface;
use Application\ImportBundle\Writer\WriterInterface;
use DpSys\LowError\SystemErrorHandler;
use JMS\Serializer\Serializer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Generator importer service
 * Data exporter (what we call "generators") from 3rd party systems.
 *
 * Class Generator
 */
class Importer implements ImporterInterface
{
    /**
     * @var ExporterInterface
     */
    private $exporter;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ExporterInterface  $exporter
     * @param ValidatorInterface $validator
     * @param WriterInterface    $writer
     * @param Serializer         $serializer
     * @param LoggerInterface    $logger
     */
    public function __construct(
        ExporterInterface  $exporter,
        ValidatorInterface $validator,
        WriterInterface    $writer,
        Serializer         $serializer,
        LoggerInterface    $logger
    ) {
        $this->exporter   = $exporter;
        $this->writer     = $writer;
        $this->validator  = $validator;
        $this->serializer = $serializer;
        $this->logger     = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRecordsCount(ImporterContext $context)
    {
        $count = 0;
        foreach (ImporterContext::getOrderedTypes() as $entityClass) {
            $count += $this->exporter->getCountByType($context, $entityClass);
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ImporterContext $context)
    {
        $collection = new ImporterCollection();

        // Exports data to a collection of entities
        foreach (ImporterContext::getOrderedTypes() as $type) {
            $this->logger->info('');
            $this->logger->info('=====================================');
            $this->logger->info(sprintf('Export `%s` collection', $type));
            $this->logger->info('=====================================');

            $collection->attach($type, $this->exporter->exportByType($context, $type));
        }

        // Writes batch config (even no entities to support "retry-after" timeout)
        // Writes batch config before validation to skip broken batches
        $newBatchConfig = $this->exporter->getNextBatchConfig($context);
        @file_put_contents($context->getBatchFilePath(), $this->serializer->serialize($newBatchConfig, 'json'));

        if ($collection->hasEntities()) {
            // Validate the collection of entities
            foreach (ImporterContext::getOrderedTypes() as $type) {
                if ($collection->hasEntitiesByType($type)) {
                    foreach ($collection->getByType($type) as $model) {
                        $errors = $this->validator->validate($model);
                        if (count($errors)) {
                            // Removing broken entities
                            $collection->detach($model);
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

            // Writes entities to a storage
            foreach (ImporterContext::getOrderedTypes() as $type) {
                if ($collection->hasEntitiesByType($type)) {
                    $this->logger->info('');
                    $this->logger->info('=====================================');
                    $this->logger->info(sprintf('Write `%s` collection', $type));
                    $this->logger->info('=====================================');

                    foreach ($collection->getByType($type) as $model) {
                        $this->writer->writeData($context, $model);
                    }
                }
            }
        }
    }
}
