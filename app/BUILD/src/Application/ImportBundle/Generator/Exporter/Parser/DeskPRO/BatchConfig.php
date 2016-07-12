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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\ImportBundle\Generator\Exporter\ExporterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractBatchSizeConfig;

/**
 * DeskPRO batch configuration.
 *
 * Class BatchConfig
 */
final class BatchConfig extends AbstractBatchSizeConfig
{
    /**
     * @var int
     */
    private $users_min_id = 0;

    /**
     * @var int
     */
    private $tickets_min_id = 0;

    /**
     * {@inheritdoc}
     */
    public function getExporterType()
    {
        return ExporterInterface::TYPE_DESKPRO;
    }

    /**
     * Returns users table offset.
     *
     * @return int
     */
    public function getUsersMinId()
    {
        return $this->users_min_id;
    }

    /**
     * Set users table offset.
     *
     * @param int $min_id
     *
     * @return $this
     */
    public function setUsersMinId($min_id)
    {
        $this->users_min_id = (int) $min_id;

        return $this;
    }

    /**
     * Returns tickets table offset.
     *
     * @return int
     */
    public function getTicketsMinId()
    {
        return $this->tickets_min_id;
    }

    /**
     * Set tickets table offset.
     *
     * @param int $min_id
     *
     * @return $this
     */
    public function setTicketsMinId($min_id)
    {
        $this->tickets_min_id = (int) $min_id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'users_min_id'   => $this->users_min_id,
            'tickets_min_id' => $this->tickets_min_id,
        ]);
    }
}
