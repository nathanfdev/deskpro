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

namespace Application\ImportBundle\Generator\Writer;

use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\Exporter\Parser\BatchConfigInterface;
use RuntimeException;

/**
 * Base generator writer.
 *
 * Class AbstractWriter
 */
abstract class AbstractWriter extends AbstractGenerator implements WriterInterface
{
    /**
     * @var BatchConfigInterface
     */
    protected $batch_config;

    /**
     * @var array
     */
    protected $entity_types = array();

    /**
     * {@inheritdoc}
     */
    public function setBatchConfig(BatchConfigInterface $config)
    {
        $this->batch_config = $config;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setWritingEntityTypes(array $types)
    {
        $this->entity_types = $types;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function writeBatchConfig()
    {
        if (!$this->config) {
            throw new RuntimeException('Generator configuration is not defined');
        }
        if (!$this->batch_config) {
            throw new RuntimeException('Batch configuration is not defined');
        }
        if (!$this->config->getBatchFilePath()) {
            throw new RuntimeException('Batch config file path is not defined');
        }

        $this->createDirIfNotExist($this->config->getBatchFileDir());
        $this->logInfo('');

        if (!$this->writeJsonFile($this->batch_config->toArray(), $this->config->getBatchFilePath())) {
            throw new RuntimeException('Unable to create batch config file');
        }

        return true;
    }

    /**
     * Creates output directory if not exist.
     *
     * @throws RuntimeException
     */
    protected function createOutputDirIfNotExist()
    {
        if (!$this->config) {
            throw new RuntimeException('Generator configuration is not defined');
        }
        if (!$this->config->getOutputPath()) {
            throw new RuntimeException('Output path is not defined');
        }

        $this->createDirIfNotExist($this->config->getOutputPath());
    }

    /**
     * Creates a directory if not exist.
     *
     * @param string $dir
     *
     * @throws RuntimeException
     */
    protected function createDirIfNotExist($dir)
    {
        if (is_dir($dir) === false) {
            if (mkdir($dir, 0777, true) === false) {
                throw new RuntimeException(sprintf('Unable to create dir `%s`', $dir));
            }
        }
    }

    /**
     * Writes data to json file
     * Returns true on success.
     *
     * @param array  $data
     * @param string $path
     *
     * @return bool
     */
    protected function writeJsonFile(array $data, $path)
    {
        $data = @json_encode($data);

        if ($this->config->isDryRun()) {
            $this->logDebug(sprintf('Dry run mode is enabled, filename `%s` is not created or updated.', $path));
        } else {
            if (@file_exists($path)) {
                $this->logInfo(sprintf('File `%s` already exists (Override).', $path));
            } else {
                $this->logInfo(sprintf('Generate a new file `%s`', $path));
            }
            if (@file_put_contents($path, $data) === false) {
                $this->logError(sprintf('Unable to write file `%s`', $path));

                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public static function getOrderedTypes()
    {
        return array(
            EntityInterface::TYPE_ORGANIZATION_CUSTOM_DEF,
            EntityInterface::TYPE_TICKET_CUSTOM_DEF,
            EntityInterface::TYPE_PERSON_CUSTOM_DEF,
            EntityInterface::TYPE_ARTICLE_CUSTOM_DEF,
            EntityInterface::TYPE_FEEDBACK_CUSTOM_DEF,
            EntityInterface::TYPE_ORGANIZATION,
            EntityInterface::TYPE_PERSON,
            EntityInterface::TYPE_TICKET,
            EntityInterface::TYPE_ARTICLE_CATEGORY,
            EntityInterface::TYPE_ARTICLE,
            EntityInterface::TYPE_DOWNLOAD,
            EntityInterface::TYPE_FEEDBACK,
            EntityInterface::TYPE_NEWS,
        );
    }
}
