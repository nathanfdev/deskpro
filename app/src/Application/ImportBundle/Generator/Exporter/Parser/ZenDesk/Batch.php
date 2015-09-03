<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractBatchParser;
use DateTime;

/**
 * ZenDesk batch parser
 *
 * Class Batch
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
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
    public function validate(array $config)
    {
        $columns = array(
            'people_end_time',
            'tickets_end_time',
            'articles_end_time',
            'retry_after_time',
        );

        return parent::validate($config) && $this->hasRequiredColumns($config, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $config)
    {
        /** @var BatchConfig $batch_config */
        $batch_config = parent::parse($config);

        if ($config['people_end_time']) {
            $batch_config->setPeopleEndTime(new DateTime($config['people_end_time']));
        }
        if ($config['tickets_end_time']) {
            $batch_config->setTicketsEndTime(new DateTime($config['tickets_end_time']));
        }
        if ($config['articles_end_time']) {
            $batch_config->setArticlesEndTime(new DateTime($config['articles_end_time']));
        }
        if ($config['retry_after_time']) {
            $batch_config->setRetryAfterTime(new DateTime($config['retry_after_time']));
        }
        if ($config['has_remaining']) {
            $batch_config->setHasRemaining($config['has_remaining']);
        } else {
            $batch_config->setHasRemaining(false);
        }

        return $batch_config;
    }
}
