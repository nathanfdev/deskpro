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
use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\BatchConfigInterface;
use Application\ImportBundle\Generator\Writer\WriterInterface;
use Application\ImportBundle\Reader\ReaderConfigInterface;
use DateTime;
use Exception;

/**
 * Configuration of generator importer service.
 *
 * Class GeneratorConfig
 */
class GeneratorConfig
{
    /**
     * @var string
     */
    private $exporter_type;

    /**
     * @var BatchConfigInterface
     */
    private $exporter_batch_config;

    /**
     * @var string
     */
    private $writer_type;

    /**
     * @var string
     */
    private $input_path;

    /**
     * @var string
     */
    private $output_path;

    /**
     * @var string
     */
    private $log_path = '/import.log';

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * @var bool
     */
    private $dry_run = false;

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * @var ReaderConfigInterface
     */
    protected $reader_config;

    /**
     * Returns an exporter type.
     *
     * @return string
     */
    public function getExporterType()
    {
        return $this->exporter_type;
    }

    /**
     * Does defined exporter support for batching?
     *
     * @throws Exception
     *
     * @return bool
     */
    public function isBatchExporter()
    {
        if (!$this->exporter_type) {
            throw new Exception('Exporter type is not defined');
        }

        $batch_exporters = [
            ExporterInterface::TYPE_JSON,
            ExporterInterface::TYPE_OS_TICKET,
            ExporterInterface::TYPE_ZENDESK,
            ExporterInterface::TYPE_DESKPRO,
        ];

        return in_array($this->exporter_type, $batch_exporters, true);
    }

    /**
     * Set an exporter type
     * Supported types are csv, json, osticket and zendesk.
     *
     * @param string $exporter_type
     *
     * @return $this
     */
    public function setExporterType($exporter_type)
    {
        $this->exporter_type = $exporter_type;

        return $this;
    }

    /**
     * Returns exporter batch config.
     *
     * @return BatchConfigInterface
     */
    public function getExporterBatchConfig()
    {
        return $this->exporter_batch_config;
    }

    /**
     * Returns retry after timeout.
     *
     * @return int
     */
    public function getRetryWaitTimeout()
    {
        $current_time = new DateTime();

        if ($this->exporter_batch_config) {
            $config = $this->exporter_batch_config;

            if ($config instanceof Exporter\Parser\BatchRetryAfterConfigInterface && $config->getRetryAfterTime()) {
                if ($config->getRetryAfterTime() > $current_time) {
                    return $config->getRetryAfterTime()->getTimestamp() - $current_time->getTimestamp();
                }
            }
        }

        return 0;
    }

    /**
     * Set exporter batch config.
     *
     * @param BatchConfigInterface $config
     *
     * @throws Exception
     *
     * @return $this
     */
    public function setExporterBatchConfig(BatchConfigInterface $config = null)
    {
        if ($config) {
            if (!$this->exporter_type) {
                throw new Exception('Exporter type is not defined');
            }
            if ($this->exporter_type !== $config->getExporterType()) {
                throw new Exception(sprintf(
                    'Unable to set up batch config, it is supposed to be `%s`, `%s` given',
                    $this->exporter_type, $config->getExporterType()
                ));
            }
        }

        $this->exporter_batch_config = $config;

        return $this;
    }

    /**
     * Returns generation type.
     *
     * @return string
     */
    public function getGenerationType()
    {
        if ($this->writer_type === Writer\WriterInterface::TYPE_JSON) {
            return 'Exporting';
        }

        return 'Importing';
    }

    /**
     * Returns a writer type.
     *
     * @return string
     */
    public function getWriterType()
    {
        return $this->writer_type;
    }

    /**
     * Returns true if a writer is specified.
     *
     * @return bool
     */
    public function hasWriter()
    {
        return $this->writer_type !== null;
    }

    /**
     * Set a writer type
     * Supported types are json, deskpro.
     *
     * @param string $writer_type
     *
     * @return $this
     */
    public function setWriterType($writer_type)
    {
        $this->writer_type = $writer_type;

        return $this;
    }

    /**
     * Returns a collection of entity types to be affected by the importer tool.
     *
     * @return string[]
     */
    public function getEntityTypes()
    {
        return [
            Entity\EntityInterface::TYPE_ORGANIZATION,
            Entity\EntityInterface::TYPE_ORGANIZATION_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_TICKET,
            Entity\EntityInterface::TYPE_PERSON,
            Entity\EntityInterface::TYPE_PERSON_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_TICKET_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_ARTICLE,
            Entity\EntityInterface::TYPE_ARTICLE_CATEGORY,
            Entity\EntityInterface::TYPE_ARTICLE_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_DOWNLOAD,
            Entity\EntityInterface::TYPE_FEEDBACK,
            Entity\EntityInterface::TYPE_FEEDBACK_CUSTOM_DEF,
            Entity\EntityInterface::TYPE_NEWS,
        ];
    }

    /**
     * Check if config has an entity type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasEntityType($type)
    {
        return in_array($type, $this->getEntityTypes(), true);
    }

    /**
     * Input path of exporting data.
     *
     * @return string
     */
    public function getInputPath()
    {
        return $this->input_path;
    }

    /**
     * Some of the exporters need an input path
     * Returns true if the input path must be specified.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function needInputPath()
    {
        if (!$this->exporter_type) {
            throw new Exception('Exporter type is not defined');
        }

        $types = [
            Exporter\ExporterInterface::TYPE_CSV,
            Exporter\ExporterInterface::TYPE_JSON,
        ];

        return in_array($this->exporter_type, $types, true);
    }

    /**
     * Set an input path.
     *
     * @param string $input_path
     *
     * @return $this
     */
    public function setInputPath($input_path)
    {
        $this->input_path = $input_path ? (rtrim($input_path, '/').'/') : null;

        return $this;
    }

    /**
     * Returns an output path
     * Uses to collect generated json files.
     *
     * @return string
     */
    public function getOutputPath()
    {
        return $this->output_path;
    }

    /**
     * Set an output path.
     *
     * @param string $output_path
     *
     * @return $this
     */
    public function setOutputPath($output_path)
    {
        $this->output_path = $output_path ? (rtrim($output_path, '/').'/') : null;

        return $this;
    }

    /**
     * Returns batch file dir location.
     *
     * @return null|string
     */
    public function getBatchFileDir()
    {
        if ($this->output_path) {
            return $this->output_path;
        } else {
            if ($this->input_path) {
                return $this->input_path;
            }
        }

        return;
    }

    /**
     * Returns batch file path location.
     *
     * @return null|string
     */
    public function getBatchFilePath()
    {
        if ($this->output_path) {
            return $this->output_path.WriterInterface::OUTPUT_BATCH_FILE;
        } else {
            if ($this->input_path) {
                return $this->input_path.WriterInterface::INPUT_BATCH_FILE;
            }
        }

        return;
    }

    /**
     * Returns a log path.
     *
     * @return string
     */
    public function getLogPath()
    {
        return $this->log_path;
    }

    /**
     * Set a log path.
     *
     * @param string $log_path
     *
     * @return $this
     */
    public function setLogPath($log_path)
    {
        $this->log_path = $log_path;

        return $this;
    }

    /**
     * Is verbose mode enabled.
     *
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * Set verbose mode
     * All output messages are shown in console.
     *
     * @param bool $verbose
     *
     * @return $this
     */
    public function setVerbose($verbose)
    {
        $this->verbose = (bool) $verbose;

        return $this;
    }

    /**
     * Returns true if a writer does not flush data.
     *
     * @return bool
     */
    public function isDryRun()
    {
        return $this->dry_run;
    }

    /**
     * Set a writer not to flush data.
     *
     * @param bool $dry_run
     *
     * @return $this
     */
    public function setDryRun($dry_run)
    {
        $this->dry_run = (bool) $dry_run;

        return $this;
    }

    /**
     * Is silent mode.
     *
     * @return bool
     */
    public function isSilent()
    {
        return $this->silent;
    }

    /**
     * Set silent mode
     * No progressbar or output messages.
     *
     * @param bool $silent
     *
     * @return $this
     */
    public function setSilent($silent)
    {
        $this->silent = (bool) $silent;

        return $this;
    }

    /**
     * Shows progressbar.
     *
     * @return bool
     */
    public function isProgressbarEnabled()
    {
        return $this->verbose === false && $this->silent === false;
    }

    /**
     * Shows output.
     *
     * @return bool
     */
    public function isConsoleOutputEnabled()
    {
        return $this->verbose && $this->silent === false;
    }

    /**
     * @return ReaderConfigInterface
     */
    public function getReaderConfig()
    {
        return $this->reader_config;
    }

    /**
     * @param ReaderConfigInterface $reader_config
     *
     * @return $this
     */
    public function setReaderConfig(ReaderConfigInterface $reader_config)
    {
        $this->reader_config = $reader_config;

        return $this;
    }
}
