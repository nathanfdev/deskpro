<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LegacyTicketFilterSet.
 *
 * @JMS\ExclusionPolicy("all")
 */
class LegacyTicketFilterSet
{
    const TYPE_AWAITING_AGENT = 1;
    const TYPE_ALL_TICKETS    = 2;
    const TYPE_CUSTOM_FILTERS = 3;

    /**
     * The unique ID of filter set.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Title for current filter set.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * It's display order.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $display_order;

    /**
     * Filters that belons this filter set.
     *
     * @JMS\Expose()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\LegacyTicketFilter>>")
     *
     * @var LegacyTicketFilter[]
     */
    protected $filters = [];

    /**
     * True if this filter set is default one.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_default = false;

    /**
     * Person id if this stuff belongs to somebody privately.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $private_agent;

    /**
     * Ids of agent shares this filter set.
     *
     * @JMS\Expose()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]
     */
    protected $shared_agents = [];

    /**
     * Constructor.
     *
     * @param int    $id
     * @param string $title
     * @param int    $display_order
     * @param bool   $is_default
     */
    public function __construct($id, $title, $display_order = 0, $is_default = true)
    {
        $this->id            = $id;
        $this->title         = $title;
        $this->display_order = $display_order;
        $this->is_default    = $is_default;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @return LegacyTicketFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param LegacyTicketFilter $filter
     *
     * @return $this
     */
    public function addFilter(LegacyTicketFilter $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * @return Person
     */
    public function getPrivateAgent()
    {
        return $this->private_agent;
    }

    /**
     * @return Person[]
     */
    public function getSharedAgents()
    {
        return $this->shared_agents;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function addSharedAgent(Person $person)
    {
        $this->shared_agents[] = $person;

        return $this;
    }
}
