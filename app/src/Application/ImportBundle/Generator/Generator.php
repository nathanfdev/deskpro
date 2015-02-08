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
class Generator extends AbstractGenerator implements GeneratorInterface
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

        foreach ($this->config->getEntityTypes() as $type) {
            $collection = $exporter->exportByType($type);
            $exceptions = $this->validateExportingCollection($type, $collection);
            if (count($exceptions) > 0) {
                throw new GeneratorException($exceptions);
            }

            foreach ($collection as $entity) {
                /** @var Entity\EntityInterface $entity */
                $outputWriter->writeData($entity);
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
            throw new Exception('Generator configuration is not set up');
        }

        foreach ($this->exporters as $exporter) {
            /** @var Exporter\ExporterInterface $exporter */
            if ($exporter->getType() === $this->config->getExporterType()) {
                $exporter->setConfig($this->config);

                if ($this->logger && $exporter instanceof LoggerAwareInterface) {
                    /** @var LoggerAwareInterface $exporter */
                    $exporter->setLogger($this->logger);
                }
                if ($this->progress_bar && $exporter instanceof ProgressBarAwareInterface) {
                    /** @var ProgressBarAwareInterface $exporter */
                    $exporter->setProgressBarHelper($this->progress_bar);
                }

                $this->logNotice(sprintf('Get `%s` exporter', $exporter->getType()));
                return $exporter;
            }
        }

        throw new Exception(sprintf('Generator exporter `%s` not found', $this->config->getExporterType()));
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
            throw new Exception('Generator configuration is not set up');
        }

        foreach ($this->writers as $writer) {
            /** @var Writer\WriterInterface $writer */
            if ($writer->getType() === $this->config->getWriterType()) {
                $writer->setConfig($this->config);

                if ($this->logger && $writer instanceof LoggerAwareInterface) {
                    /** @var LoggerAwareInterface $writer */
                    $writer->setLogger($this->logger);
                }
                if ($this->progress_bar && $writer instanceof ProgressBarAwareInterface) {
                    /** @var ProgressBarAwareInterface $writer */
                    $writer->setProgressBarHelper($this->progress_bar);
                }

                $this->logNotice(sprintf('Get `%s` writer', $writer->getType()));
                return $writer;
            }
        }

        throw new Exception(sprintf('Generator writer `%s` not found', $this->config->getExporterType()));
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
}
