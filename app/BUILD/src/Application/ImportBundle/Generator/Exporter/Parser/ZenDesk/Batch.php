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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractBatchParser;

/**
 * ZenDesk batch parser.
 *
 * Class Batch
 */
final class Batch extends AbstractBatchParser
{
    /**
     * {@inheritdoc}
     */
    public function getExporterType()
    {
        return ExporterInterface::TYPE_ZENDESK;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBatchConfig()
    {
        return new BatchConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'people_end_time'   => TransformerInterface::TYPE_DATE,
            'tickets_end_time'  => TransformerInterface::TYPE_DATE,
            'articles_end_time' => TransformerInterface::TYPE_DATE,
            'retry_after_time'  => TransformerInterface::TYPE_DATE,
        ]);

        /** @var BatchConfig $config */
        $config = parent::parse($data);
        $config
            ->setPeopleEndTime($formatted['people_end_time'])
            ->setTicketsEndTime($formatted['tickets_end_time'])
            ->setArticlesEndTime($formatted['articles_end_time'])
            ->setRetryAfterTime($formatted['retry_after_time'])
        ;

        return $config;
    }
}
