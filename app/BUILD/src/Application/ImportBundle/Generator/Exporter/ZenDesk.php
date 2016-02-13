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
use DateTime;
use Exception;

/**
 * Exporter from ZenDesk service.
 *
 * Class ZenDesk
 */
final class ZenDesk extends AbstractExporter implements ExporterBatchInterface
{
    /**
     * @var DateTime
     */
    private $retry_date;

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE_ZENDESK;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByType($type)
    {
        try {
            return parent::getCountByType($type);
        } catch (RetryAfterException $e) {
            $this->retry_date = $e->getRetryAfterTime();
            $this->logWarning(sprintf(
                'Unable to get count of `%s` because of ZenDesk rate limits, retry after `%d` seconds',
                $type, $e->getTimeout()
            ));
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function exportByType($type)
    {
        try {
            return parent::exportByType($type);
        } catch (RetryAfterException $e) {
            $this->retry_date = $e->getRetryAfterTime();
            $this->logWarning(sprintf(
                'Unable to get a collection of `%s` because of ZenDesk rate limits, retry after `%d` seconds',
                $type, $e->getTimeout()
            ));
        }

        return new Entity\Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedBatchConfig()
    {
        /** @var Parser\ZenDesk\Tickets $tickets_parser */
        $tickets_parser = $this->getParserByType(Entity\EntityInterface::TYPE_TICKET);
        /** @var Parser\ZenDesk\People $people_parser */
        $people_parser = $this->getParserByType(Entity\EntityInterface::TYPE_PERSON);
        /** @var Parser\ZenDesk\Articles $article_parser */
        $article_parser = $this->getParserByType(Entity\EntityInterface::TYPE_ARTICLE);

        /** @var Parser\ZenDesk\BatchConfig $updated_config */
        $updated_config = clone $this->config->getExporterBatchConfig();
        $updated_config
            ->setId($updated_config->getId() + 1)
            ->setDateModified(new DateTime())
            ->setRetryAfterTime($this->retry_date)
            ->setHasRemaining(
                $tickets_parser->getCount() > 1 ||
                $people_parser->getCount()  > 1 ||
                $article_parser->getCount() > 1
            );

        if ($tickets_parser->getCurrentEndTime()) {
            $updated_config->setTicketsEndTime($tickets_parser->getCurrentEndTime());
        }
        if ($people_parser->getCurrentEndTime()) {
            $updated_config->setPeopleEndTime($people_parser->getCurrentEndTime());
        }
        if ($article_parser->getCurrentEndTime()) {
            $updated_config->setArticlesEndTime($article_parser->getCurrentEndTime());
        }

        return $updated_config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBatchConfig()
    {
        return new Parser\ZenDesk\BatchConfig();
    }

    /**
     * Returns batch config.
     *
     * @throws Exception
     *
     * @return Parser\ZenDesk\BatchConfig
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new Exception('Batch config is not defined');
    }
}
