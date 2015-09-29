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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures;

use DateTime;
use Psr\Log\LoggerInterface;

/**
 * ZenDesk fixture interface.
 *
 * Interface FixturesInterface
 */
interface FixtureInterface
{
    const COUNT = 5000;

    /**
     * Returns fixture entity type.
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Set logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * Creates fixtures by period.
     *
     * @param int      $offset
     * @param DateTime $initial_time
     * @param DateTime $end_time
     *
     * @throws \Zendesk\API\ResponseException
     *
     * @return bool
     */
    public function create($offset, DateTime $initial_time, DateTime $end_time);
}
