<?php

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
        return array_values($this->addAgentIds);
    }

    /**
     * @return int[]
     */
    public function getDelAgentIds()
    {
        return array_values($this->delAgentIds);
    }

    /**
     * @return int[]
     */
    public function getAfterMatchAgentIds()
    {
        return array_values($this->afterMatchAgentIds);
    }

    /**
     * @return int[]
     */
    public function getBeforeMatchAgentIds()
    {
        return array_values($this->beforeMatchAgentIds);
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
