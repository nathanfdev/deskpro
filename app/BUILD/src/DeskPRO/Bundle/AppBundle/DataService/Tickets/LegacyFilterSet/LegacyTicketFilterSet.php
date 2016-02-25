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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;

/**
 * Class LegacyTicketFilterSet.
 */
class LegacyTicketFilterSet
{
    const ID_AWAITING_AGENT = 1;
    const ALL_TICKETS       = 2;
    const FILTERS           = 3;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $display_order;

    /**
     * @var LegacyTicketFilter[]
     */
    protected $filters = [];

    /**
     * @var bool
     */
    protected $is_default = false;

    /**
     * @var Person
     */
    protected $private_agent;

    /**
     * @var Person[]
     */
    protected $shared_agents = [];

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
}
