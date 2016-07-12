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

namespace Application\ImportBundle\Generator;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\AbstractExporter;
use Application\ImportBundle\Generator\Validator\ExceptionCollection;
use Application\ImportBundle\Generator\Validator\ValidatorExceptionInterface;
use Application\ImportBundle\Generator\Writer\AbstractWriter;
use Application\ImportBundle\Importer\Importer;
use Doctrine\DBAL\DBALException;
use DpSys\LowError\SystemErrorHandler;
use Exception;

/**
 * Generator importer service
 * Data exporter (what we call "generators") from 3rd party systems.
 *
 * Class Generator
 */
final class Generator extends AbstractGenerator implements GeneratorInterface, ExporterAwareInterface
{
    /**
     * @var Exporter\AbstractExporter
     */
    private $exporter;

    /**
     * @var \Application\ImportBundle\Generator\Validator\Collection
     */
    private $validators;

    /**
     * @var Writer\WriterInterface
     */
    private $writer;

    /**
     * @var Importer
     */
    protected $importer;

    /**
     * Constructor.
     *
     * @param Exporter\ExporterInterface $exporter
     * @param Validator\Collection       $validators
     * @param GeneratorConfig            $config
     * @param Importer                   $importer
     * @param Writer\WriterInterface     $writer
     */
    public function __construct(
        Exporter\ExporterInterface $exporter,
        Validator\Collection       $validators,
        GeneratorConfig            $config,
        Importer                   $importer,
        Writer\WriterInterface     $writer = null
    ) {
        $this->config     = $config;
        $this->exporter   = $exporter;
        $this->writer     = $writer;
        $this->importer   = $importer;
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRecordsCount()
    {
        $count    = 0;
        $exporter = $this->getExporter();

        foreach ($this->getRequiredExportersOrderedEntityTypes() as $record_type) {
            $count += $exporter->getCountByType($record_type);
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        try {
            $exporter     = $this->getExporter();
            $outputWriter = $this->getWriter();
            $collection   = new GenerateCollection();

            $this->importer->setStatus($this->config->getExporterType(), self::STATUS_EXPORT);

            // Exports data to a collection of entities
            foreach ($this->getRequiredExportersOrderedEntityTypes() as $type) {
                $this->exporterLogHeader($type);
                $collection->attach($type, $exporter->exportByType($type));
            }

            // Writes batch config (even no entities to support "retry-after" timeout)
            // Writes batch config before validation to skip broken batches

            if ($exporter instanceof Exporter\ExporterBatchInterface) {
                $outputWriter->setBatchConfig($exporter->getUpdatedBatchConfig());
                $outputWriter->writeBatchConfig();
            }

            if ($collection->hasEntities()) {
                $this->importer->setStatus($this->config->getExporterType(), self::STATUS_VALIDATION);

                // Validate the collection of entities
                foreach ($this->getRequiredWritersOrderedEntityTypes() as $type) {
                    if ($collection->hasEntitiesByType($type)) {
                        $entities   = $collection->getByEntityType($type);
                        $exceptions = $this->validateExportingCollection($type, $entities);

                        if (count($exceptions) > 0) {
                            foreach ($exceptions as $exception) {
                                // Removing broken entities
                                $collection->detach($exception->getEntity());
                                $this->logAlert(sprintf(
                                    'Validator failure for %s on record #%s: %s',

                                    get_class($exception->getEntity()),
                                    $exception->getEntity()->getOid(),
                                    $exception->getErrors())
                                );

                                $this->logInfo(json_encode($exception->getEntity()->toArray()));

                                $raw_data = $exception->getEntity()->getRawData();
                                if ($raw_data) {
                                    foreach (explode("\n", SystemErrorHandler::varToString($raw_data, 2)) as $line) {
                                        $this->logInfo($line);
                                    }
                                }
                            }
                        }
                    }
                }

                $this->importer->setStatus($this->config->getExporterType(), self::STATUS_IMPORT);

                // Writes entities to a storage
                $outputWriter->setWritingEntityTypes($collection->getContainingEntityTypes());
                $outputWriter->prepare();

                foreach ($this->getRequiredWritersOrderedEntityTypes() as $type) {
                    if ($collection->hasEntitiesByType($type)) {
                        $this->writerLogHeader($type);
                        $entities = $collection->getByEntityType($type);

                        foreach ($entities as $entity) {
                            $this->advanceProgressBar();
                            $outputWriter->writeData($entity);
                        }
                    }
                }
            }

            if ($this->progress_bar && $collection->getSkippedCount()) {
                $skip_count = $collection->getSkippedCount() * 2;
                while ($skip_count-- > 0) {
                    $this->advanceProgressBar();
                }
            }

            if ($exporter instanceof Exporter\ExporterBatchInterface) {
                if (!$exporter->getUpdatedBatchConfig()->getHasRemaining()) {
                    $this->importer->setStatus($this->config->getExporterType(), self::STATUS_DONE);
                }
            } else {
                $this->importer->setStatus($this->config->getExporterType(), self::STATUS_DONE);
            }
        } catch (\Exception $e) {
            if (!$e instanceof DBALException) {
                $this->importer->setStatus($this->config->getExporterType(), self::STATUS_ERROR);
            }
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $exporter   = $this->getExporter();
        $exceptions = new ExceptionCollection();

        foreach ($this->getRequiredExportersOrderedEntityTypes() as $type) {
            $this->exporterLogHeader($type);

            $collection = $exporter->exportByType($type);
            $exceptions->merge($this->validateExportingCollection($type, $collection));

            if ($this->progress_bar && $collection->getSkippedCount()) {
                $this->progress_bar->advance($collection->getSkippedCount());
            }
        }

        return $exceptions;
    }

    /**
     * Get exporter by configuration type.
     *
     * @throws Exception
     *
     * @return Exporter\AbstractExporter
     */
    public function getExporter()
    {
        $this->setHelpers($this->exporter);

        return $this->exporter;
    }

    /**
     * Returns a writer.
     *
     * @throws Exception
     *
     * @return Writer\WriterInterface|mixed
     */
    private function getWriter()
    {
        $this->setHelpers($this->writer);

        return $this->writer;
    }

    /**
     * Attach helpers to handler.
     *
     * @param mixed $handler
     */
    private function setHelpers($handler)
    {
        if ($this->config && $handler instanceof GeneratorConfigAwareInterface) {
            $handler->setConfig($this->config);
        }
        if ($this->logger && $handler instanceof LoggerAwareInterface) {
            $handler->setLogger($this->logger);
        }
        if ($this->progress_bar && $handler instanceof ProgressBarAwareInterface) {
            $handler->setProgressBarHelper($this->progress_bar);
        }
    }

    /**
     * Validates exporting collection.
     *
     * @param string            $type
     * @param Entity\Collection $collection
     *
     * @return ExceptionCollection|ValidatorExceptionInterface[]
     */
    private function validateExportingCollection($type, Entity\Collection $collection)
    {
        $exceptions = new ExceptionCollection();
        $validators = $this->validators->getByEntityType($type);

        foreach ($collection as $entity) {
            $this->advanceProgressBar();

            foreach ($validators as $validator) {
                try {
                    $validator->validate($entity);
                } catch (ValidatorExceptionInterface $e) {
                    $exceptions->attach($e);
                }
            }
        }

        return $exceptions;
    }

    /**
     * Writes exporter log header.
     *
     * @param string $type
     */
    private function exporterLogHeader($type)
    {
        $this->logInfo('');
        $this->logInfo('=====================================');
        $this->logInfo(sprintf('Export `%s` collection', $type));
        $this->logInfo('=====================================');
    }

    /**
     * Writes output writer log header.
     *
     * @param string $type
     */
    private function writerLogHeader($type)
    {
        $this->logInfo('');
        $this->logInfo('=====================================');
        $this->logInfo(sprintf('Write `%s` collection', $type));
        $this->logInfo('=====================================');
    }

    /**
     * Returns ordered entity types of the exporters.
     *
     * @throws Exception
     *
     * @return string[]
     */
    private function getRequiredExportersOrderedEntityTypes()
    {
        return $this->getRequiredOrderedEntityTypes(AbstractExporter::getOrderedTypes());
    }

    /**
     * Returns ordered entity types of the writers.
     *
     * @throws Exception
     *
     * @return string[]
     */
    private function getRequiredWritersOrderedEntityTypes()
    {
        return $this->getRequiredOrderedEntityTypes(AbstractWriter::getOrderedTypes());
    }

    /**
     * Returns a list of ordered entity types
     * Writers and exporters need different entities foreach order.
     *
     * @param array $types
     *
     * @throws Exception
     *
     * @return array
     */
    private function getRequiredOrderedEntityTypes(array $types)
    {
        if (!$this->config) {
            throw new Exception('Generator configuration is not defined');
        }

        $allowed_types = [];
        foreach ($types as $type) {
            if ($this->config->hasEntityType($type)) {
                $allowed_types[] = $type;
            }
        }

        return $allowed_types;
    }
}
