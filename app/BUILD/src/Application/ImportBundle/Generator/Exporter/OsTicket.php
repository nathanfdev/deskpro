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

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\Entity;
use DateTime;
use Exception;

/**
 * Exporter from OsTicket service.
 *
 * Class OsTicket
 */
final class OsTicket extends AbstractExporter implements ExporterBatchInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE_OS_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType($type)
    {
        if ($this->getBatchConfig()->getBatchSize() === 0) {
            throw new Exception('Invalid batch size defined');
        }

        $count = parent::getCountByType($type);
        if ($count < $this->getBatchConfig()->getBatchSize()) {
            return $count;
        }

        return $this->getBatchConfig()->getBatchSize();
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType($type)
    {
        if ($this->getBatchConfig()->getBatchSize() === 0) {
            throw new Exception('Invalid batch size defined');
        }

        return parent::exportByType($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedBatchConfig()
    {
        /** @var Parser\OsTicket\Tickets $tickets_parser */
        $tickets_parser = $this->getParserByType(Entity\EntityInterface::TYPE_TICKET);
        /** @var Parser\OsTicket\People $people_parser */
        $people_parser = $this->getParserByType(Entity\EntityInterface::TYPE_PERSON);

        /** @var Parser\OsTicket\BatchConfig $updated_config */
        $updated_config = clone $this->config->getExporterBatchConfig();
        $updated_config
            ->setId($updated_config->getId() + 1)
            ->setStaffMinId($people_parser->getCurrentStaffMinId())
            ->setUsersMinId($people_parser->getCurrentUsersMinId())
            ->setTicketsMinId($tickets_parser->getCurrentTicketsMinId())
            ->setDateModified(new DateTime())
            ->setHasRemaining($tickets_parser->getCount() > 0 || $people_parser->getCount() > 0)
        ;

        return $updated_config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBatchConfig()
    {
        return new Parser\OsTicket\BatchConfig();
    }

    /**
     * Returns batch config.
     *
     * @throws Exception
     *
     * @return Parser\OsTicket\BatchConfig
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new Exception('Batch config is not defined');
    }
}
