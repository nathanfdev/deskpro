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

namespace Application\ImportBundle\Generator;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Validator;
use Exception;

/**
 * Generator importer service
 * Data exporter (what we call "generators") from 3rd party systems
 *
 * Class Generator
 * @package Application\ImportBundle\Generator
 */
final class Generator extends AbstractGenerator implements GeneratorInterface
{
    /**
     * @var Exporter\Collection
     */
    private $exporters;

    /**
     * @var Validator\Collection
     */
    private $validators;

    /**
     * @var Writer\Collection
     */
    private $writers;

    /**
     * Constructor
     *
     * @param Exporter\Collection  $exporters
     * @param Validator\Collection $validators
     * @param Writer\Collection    $writers
     */
    public function __construct(
        Exporter\Collection  $exporters,
        Validator\Collection $validators,
        Writer\Collection    $writers
    ) {
        $this->exporters  = $exporters;
        $this->validators = $validators;
        $this->writers    = $writers;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRecordsCount()
    {
        $count    = 0;
        $exporter = $this->getExporter();

        foreach ($this->config->getEntityTypes() as $record_type) {
            $count += $exporter->getCountByType($record_type);
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $exporter     = $this->getExporter();
        $outputWriter = $this->getWriter();
        $collection   = new GenerateCollection();

        // Exports data to a collection of entities
        foreach ($this->getRequiredExportersOrderedEntityTypes() as $type) {
            $this->exporterLogHeader($type);

            $entities   = $exporter->exportByType($type);
            $exceptions = $this->validateExportingCollection($type, $entities);
            if (count($exceptions) > 0) {
                throw new GeneratorException($exceptions);
            }

            $collection->attach($type, $entities);
        }

        if ($exporter instanceof Exporter\ExporterBatchInterface) {
            $outputWriter->setBatchConfig($exporter->getUpdatedBatchConfig());

            // Writes batch config (even no entities to write to support "retry-after" timeout)
            $outputWriter->writeBatchConfig();
        }

        // Writes entities to a storage
        if ($collection->hasEntities()) {
            $outputWriter->setWritingEntityTypes($collection->getContainingEntityTypes());
            $outputWriter->prepare();

            foreach ($this->getRequiredWritersOrderedEntityTypes() as $type) {
                if ($collection->hasEntitiesByType($type)) {
                    $this->writerLogHeader($type);
                    $entities = $collection->getByEntityType($type);

                    foreach ($entities as $entity) {
                        $this->advanceProgressBar();

                        /** @var Entity\EntityInterface $entity */
                        $outputWriter->writeData($entity);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $exporter   = $this->getExporter();
        $exceptions = new Validator\ExceptionCollection();

        foreach ($this->config->getEntityTypes() as $type) {
            $this->exporterLogHeader($type);

            $collection = $exporter->exportByType($type);
            $exceptions->merge($this->validateExportingCollection($type, $collection));
        }

        return $exceptions;
    }

    /**
     * Get exporter by configuration type
     *
     * @return Exporter\ExporterInterface
     * @throws Exception
     */
    private function getExporter()
    {
        if ( ! $this->config) {
            throw new Exception('Generator configuration is not defined');
        }

        $exporter = $this->exporters->getByType($this->config->getExporterType());

        if ($exporter instanceof Exporter\LazyExporter) {
            $exporter = $exporter->initialize($this->config->getReaderConfig());
        }
        if ($exporter instanceof Exporter\ExporterBatchInterface) {
            if ( ! $this->config->getExporterBatchConfig()) {
                $this->config->setExporterBatchConfig($exporter->getDefaultBatchConfig());
            }
        }

        $this->setHelpers($exporter);
        $this->logNotice(sprintf('Get `%s` exporter', $exporter->getType()));

        return $exporter;
    }

    /**
     * Returns a writer
     *
     * @return Writer\WriterInterface|mixed
     * @throws Exception
     */
    private function getWriter()
    {
        if ( ! $this->config) {
            throw new Exception('Generator configuration is not defined');
        }

        $writer = $this->writers->getByType($this->config->getWriterType());

        $this->setHelpers($writer);
        $this->logNotice(sprintf('Get `%s` writer', $writer->getType()));

        return $writer;
    }

    /**
     * Attach helpers to handler
     *
     * @param mixed $handler
     */
    private function setHelpers($handler)
    {
        if ($this->config && $handler instanceof GeneratorConfigAwareInterface) {
            /** @var GeneratorConfigAwareInterface $handler */
            $handler->setConfig($this->config);
        }
        if ($this->logger && $handler instanceof LoggerAwareInterface) {
            /** @var LoggerAwareInterface $handler */
            $handler->setLogger($this->logger);
        }
        if ($this->progress_bar && $handler instanceof ProgressBarAwareInterface) {
            /** @var ProgressBarAwareInterface $handler */
            $handler->setProgressBarHelper($this->progress_bar);
        }
    }

    /**
     * Validates exporting collection
     *
     * @param string            $type
     * @param Entity\Collection $collection
     *
     * @return Validator\ExceptionCollection
     */
    private function validateExportingCollection($type, Entity\Collection $collection)
    {
        $exceptions = new Validator\ExceptionCollection();
        $validators = $this->validators->getByRecordType($type);

        foreach ($collection as $entity) {
            $this->advanceProgressBar();

            foreach ($validators as $validator) {
                try {
                    /** @var Validator\ValidatorInterface $validator */
                    $validator->validate($entity);

                } catch (Validator\ValidatorExceptionInterface $e) {
                    $exceptions->attach($e);
                }
            }
        }

        return $exceptions;
    }

    /**
     * Writes exporter log header
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
     * Writes output writer log header
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
     * Returns ordered entity types of the exporters
     *
     * @return string[]
     * @throws Exception
     */
    private function getRequiredExportersOrderedEntityTypes()
    {
        return $this->getRequiredOrderedEntityTypes($this->exporters);
    }

    /**
     * Returns ordered entity types of the writers
     *
     * @return string[]
     * @throws Exception
     */
    private function getRequiredWritersOrderedEntityTypes()
    {
        return $this->getRequiredOrderedEntityTypes($this->writers);
    }

    /**
     * Returns a list of ordered entity types
     * Writers and exporters need different entities foreach order
     *
     * @param TypeOrderInterface $handlers
     *
     * @return string[]
     * @throws Exception
     */
    private function getRequiredOrderedEntityTypes(TypeOrderInterface $handlers)
    {
        if ( ! $this->config) {
            throw new Exception('Generator configuration is not defined');
        }

        $types = array();
        foreach ($handlers->getOrderedTypes() as $type) {
            if ($this->config->hasEntityType($type)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    public function getExporters()
    {
        return $this->exporters;
    }
}
