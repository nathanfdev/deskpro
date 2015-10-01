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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderInterface;

/**
 * Abstract DeskPRO parser.
 *
 * Class AbstractParser
 */
abstract class AbstractParser extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParser
{
    const MAX_BATCH_SIZE = 1000;

    /**
     * @var DeskPROReaderInterface
     */
    protected $reader;

    /**
     * @var int
     */
    protected $entities_loaded = 0;

    /**
     * Constructor.
     *
     * @param DeskPROReaderInterface $reader
     */
    public function __construct(DeskPROReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Returns reader batch size.
     *
     * @return int
     */
    protected function getReaderBatchSize()
    {
        $batch_size = self::MAX_BATCH_SIZE;
        if ($this->getBatchConfig()->getBatchSize() < $batch_size) {
            $batch_size = $this->getBatchConfig()->getBatchSize();
        }
        if ($this->getEntitiesLeftToLoad() < $batch_size) {
            $batch_size = $this->getEntitiesLeftToLoad();
        }

        return $batch_size;
    }

    /**
     * Returns count of entities to load in a batch.
     *
     * @return int
     */
    protected function getEntitiesLeftToLoad()
    {
        return $this->getBatchConfig()->getBatchSize() - $this->entities_loaded;
    }

    /**
     * Returns batch config.
     *
     * @throws \RuntimeException
     *
     * @return BatchConfig
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new \RuntimeException('Batch config is not defined');
    }

    /**
     * Returns custom field entity.
     *
     * @param DeskPROEntity\CustomDataAbstract $custom_data
     *
     * @return Entity\CustomField
     */
    protected function exportCustomData(DeskPROEntity\CustomDataAbstract $custom_data)
    {
        $value = $custom_data->getData();
        if ($custom_data->root_field->isChoiceType()) {
            $value = $custom_data->field->getRealTitle();
        }

        $entity = new Entity\CustomField();
        $entity
            ->setRawData($custom_data->toApiData())
            ->setOid($custom_data->getId())
            ->setDestination($entity->getDestinationPrefix().$custom_data->getId())
            ->setKey($custom_data->root_field->getRealTitle())
            ->setValue($value)
        ;

        return $entity;
    }
}
