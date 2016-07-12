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
use Application\ImportBundle\Generator\Exporter\Parser\AbstractBatchConfig;
use Application\ImportBundle\Generator\Exporter\Parser\BatchRetryAfterConfigInterface;
use DateTime;

/**
 * ZenDesk batch configuration.
 *
 * Class BatchConfig
 */
final class BatchConfig extends AbstractBatchConfig implements BatchRetryAfterConfigInterface
{
    /**
     * @var DateTime
     */
    private $people_end_time;

    /**
     * @var DateTime
     */
    private $tickets_end_time;

    /**
     * @var DateTime
     */
    private $articles_end_time;

    /**
     * @var DateTime
     */
    private $retry_after_time;

    /**
     * {@inheritdoc}
     */
    public function getExporterType()
    {
        return ExporterInterface::TYPE_ZENDESK;
    }

    /**
     * Returns people end time.
     *
     * @return DateTime
     */
    public function getPeopleEndTime()
    {
        return $this->people_end_time;
    }

    /**
     * Set people end time.
     *
     * @param DateTime $end_time
     *
     * @return $this
     */
    public function setPeopleEndTime(DateTime $end_time = null)
    {
        $this->people_end_time = $end_time;

        return $this;
    }

    /**
     * Returns tickets end time.
     *
     * @return DateTime
     */
    public function getTicketsEndTime()
    {
        return $this->tickets_end_time;
    }

    /**
     * Set tickets end time.
     *
     * @param DateTime $end_time
     *
     * @return $this
     */
    public function setTicketsEndTime(DateTime $end_time = null)
    {
        $this->tickets_end_time = $end_time;

        return $this;
    }

    /**
     * Returns articles end time.
     *
     * @return DateTime
     */
    public function getArticlesEndTime()
    {
        return $this->articles_end_time;
    }

    /**
     * Set articles end time.
     *
     * @param DateTime $end_time
     *
     * @return $this
     */
    public function setArticlesEndTime(DateTime $end_time = null)
    {
        $this->articles_end_time = $end_time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRetryAfterTime()
    {
        return $this->retry_after_time;
    }

    /**
     * {@inheritdoc}
     */
    public function setRetryAfterTime(DateTime $retry_after_time = null)
    {
        $this->retry_after_time = $retry_after_time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'people_end_time'   => $this->getDateFormatOrNull($this->people_end_time),
            'tickets_end_time'  => $this->getDateFormatOrNull($this->tickets_end_time),
            'articles_end_time' => $this->getDateFormatOrNull($this->articles_end_time),
            'retry_after_time'  => $this->getDateFormatOrNull($this->retry_after_time),
        ]);
    }
}
