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

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\NotFoundException;
use DateTime;
use Exception;

/**
 * Exporter from json files.
 *
 * Class Json
 */
final class Json extends AbstractExporter implements ExporterBatchInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE_JSON;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType($type)
    {
        try {
            return parent::getCountByType($type);
        } catch (NotFoundException $e) {
            return 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType($type)
    {
        try {
            return parent::exportByType($type);
        } catch (NotFoundException $e) {
            return new Entity\Collection();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedBatchConfig()
    {
        /** @var Parser\Json\BatchConfig $updated_config */
        $updated_config = clone $this->config->getExporterBatchConfig();
        $updated_config
            ->setId($updated_config->getId() + 1)
            ->setDateModified(new DateTime())
        ;

        if (is_dir($this->getConfig()->getInputPath().DIRECTORY_SEPARATOR.$updated_config->getId())) {
            $updated_config->setHasRemaining(true);
        } else {
            $updated_config->setHasRemaining(false);
        }

        return $updated_config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBatchConfig()
    {
        return new Parser\Json\BatchConfig();
    }

    /**
     * Returns batch config.
     *
     * @throws Exception
     *
     * @return Parser\Json\BatchConfig
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new Exception('Batch config is not defined');
    }
}
