<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Diff;

class FilterOp
{
    /**
     * @var int
     */
    private $filterId;

    /**
     * @var int[]
     */
    private $addAgentIds = [];

    /**
     * @var int[]
     */
    private $delAgentIds = [];

    /**
     * @var int[]
     */
    private $beforeMatchAgentIds = [];

    /**
     * @var int[]
     */
    private $afterMatchAgentIds = [];

    /**
     * FilterOp constructor.
     *
     * @param int   $filterId
     * @param int[] $agentIds
     */
    public function __construct($filterId)
    {
        $this->filterId = $filterId;
    }

    /**
     * @return int
     */
    public function getFilterId()
    {
        return $this->filterId;
    }

    /**
     * @param int $aid
     */
    public function addAddAgentId($aid)
    {
        $this->addAgentIds[$aid] = $aid;
    }

    /**
     * @param int $aid
     */
    public function addDelAgentId($aid)
    {
        $this->delAgentIds[$aid] = $aid;
    }

    /**
     * Records an agent where the filter matches after the change.
     * This isn't the same as an 'add' op which requires the old status to be no-match.
     *
     * This is used as an optimisation when calculating notificatin lists where match status
     * need to be calculated for subscriptions. Since this is alreaady calculated here,
     * we can re-use the value.
     *
     * @param int $aid
     */
    public function addAfterMatchAgentId($aid)
    {
        $this->afterMatchAgentIds[$aid] = $aid;
    }

    /**
     * @param int $aid
     */
    public function addBeforeMatchAgentId($aid)
    {
        $this->beforeMatchAgentIds[$aid] = $aid;
    }

    /**
     * @return int[]
     */
    public function getAddAgentIds()
    {
        return $this->addAgentIds;
    }

    /**
     * @return int[]
     */
    public function getDelAgentIds()
    {
        return $this->delAgentIds;
    }

    /**
     * @return int[]
     */
    public function getAfterMatchAgentIds()
    {
        return $this->afterMatchAgentIds;
    }

    /**
     * @return int[]
     */
    public function getBeforeMatchAgentIds()
    {
        return $this->beforeMatchAgentIds;
    }

    /**
     * @param int $aid
     *
     * @return bool
     */
    public function agentHasAfterMatch($aid)
    {
        return isset($this->afterMatchAgentIds[$aid]);
    }

    /**
     * @param int $aid
     *
     * @return bool
     */
    public function agentHasBeforeMatch($aid)
    {
        return isset($this->beforeMatchAgentIds[$aid]);
    }

    /**
     * @param int $aid
     *
     * @return bool
     */
    public function agentHasAddOp($aid)
    {
        return isset($this->addAgentIds[$aid]);
    }

    /**
     * @param int $aid
     *
     * @return bool
     */
    public function agentHasDelOp($aid)
    {
        return isset($this->delAgentIds[$aid]);
    }
}
