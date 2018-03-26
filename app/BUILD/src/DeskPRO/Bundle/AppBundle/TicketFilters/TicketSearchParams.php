<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

class TicketSearchParams
{
    const ORDER_ID                    = 'ticket.id';
    const ORDER_URGENCY               = 'ticket.urgency';
    const ORDER_PRIORITY              = 'ticket.priority';
    const ORDER_DATE_CREATED          = 'ticket.date_created';
    const ORDER_DATE_RESOLVED         = 'ticket.date_resolved';
    const ORDER_DATE_ARCHIVED         = 'ticket.date_archived';
    const ORDER_DATE_LAST_USER_REPLY  = 'ticket.date_last_user_reply';
    const ORDER_DATE_LAST_AGENT_REPLY = 'ticket.date_last_agent_reply';
    const ORDER_DATE_LAST_REPLY       = 'ticket.date_last_reply';
    const ORDER_DATE_USER_WAITING     = 'ticket.date_user_waiting';
    const ORDER_SLA_SEVERITY          = 'ticket.sla_severity';

    const GROUP_SLA_SEVERITY = 'ticket.sla_severity';
    const GROUP_AGENT        = 'ticket.agent';
    const GROUP_AGENT_TEAM   = 'ticket.agent_team';
    const GROUP_DEPARTMENT   = 'ticket.department';
    const GROUP_WORKFLOW     = 'ticket.workflow';
    const GROUP_PRIORITY     = 'ticket.priority';
    const GROUP_CATEGORY     = 'ticket.category';
    const GROUP_PRODUCT      = 'ticket.product';
    const GROUP_LANGUAGE     = 'ticket.language';

    private $availableOrderFields = [
        'ticket.id',
        'ticket.urgency',
        'ticket.priority',
        'ticket.date_created',
        'ticket.date_resolved',
        'ticket.date_archived',
        'ticket.date_last_user_reply',
        'ticket.date_last_agent_reply',
        'ticket.date_last_reply',
        'ticket.date_user_waiting',
        'ticket.sla_severity',
    ];

    private $availableGroupFields = [
        'ticket.sla_severity',
        'ticket.agent',
        'ticket.agent_team',
        'ticket.department',
        'ticket.workflow',
        'ticket.priority',
        'ticket.category',
        'ticket.product',
        'ticket.language',
    ];

    /**
     * @var array
     */
    private $orderFields = [];

    /**
     * @var string[]
     */
    private $groupFields = [];

    /**
     * @var array
     */
    private $subFilterFields = [];

    /**
     * Order results by a field. This is ignored for counts.
     *
     * @param string $fieldId
     * @param string $order
     *
     * @return $this
     */
    public function orderBy($fieldId, $order = 'ASC')
    {
        $order = strtoupper($order);

        if (!in_array($fieldId, $this->availableOrderFields)) {
            throw new \InvalidArgumentException('Invalid order field');
        }

        if ($order !== 'ASC' && $order !== 'DESC') {
            throw new \InvalidArgumentException('Invalid order direction');
        }

        $this->orderFields[] = [$fieldId, $order];

        return $this;
    }

    /**
     * Sub-filter by a grouping field. This is so a user
     * can view results based on a grouping variable.
     *
     * Sub-filters are primitive field = literal value, they do NOT
     * have the full power of FQL etc under them. They are resolved
     * on a matcher when building a query against a data source.
     *
     * @param string $fieldId
     * @param string $value
     *
     * @return $this
     */
    public function subFilterBy($fieldId, $value)
    {
        if (!in_array($fieldId, $this->availableGroupFields)) {
            throw new \InvalidArgumentException('Invalid order field');
        }

        $this->subFilterFields[] = [$fieldId, $value];

        return $this;
    }

    /**
     * Group COUNTs by this value.
     *
     * @param string $fieldId
     *
     * @return $this
     */
    public function groupBy($fieldId)
    {
        if (!in_array($fieldId, $this->availableGroupFields)) {
            throw new \InvalidArgumentException('Invalid order field');
        }

        $this->groupFields[] = $fieldId;

        return $this;
    }

    /**
     * @return array
     */
    public function getAvailableOrderFields()
    {
        return $this->availableOrderFields;
    }

    /**
     * @return array
     */
    public function getAvailableGroupFields()
    {
        return $this->availableGroupFields;
    }

    /**
     * @return array
     */
    public function getOrderFields()
    {
        return $this->orderFields;
    }

    /**
     * @return string[]
     */
    public function getGroupFields()
    {
        return $this->groupFields;
    }

    /**
     * @return array
     */
    public function getSubFilterFields()
    {
        return $this->subFilterFields;
    }

    /**
     * @return bool
     */
    public function hasOrderFields()
    {
        return !empty($this->orderFields);
    }

    /**
     * @return bool
     */
    public function hasGroupFields()
    {
        return !empty($this->groupFields);
    }

    /**
     * @return bool
     */
    public function hasSubFilterFields()
    {
        return !empty($this->subFilterFields);
    }
}
