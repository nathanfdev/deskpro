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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\LegacyTicketFilter as LegacyTicketFilterEntity;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet\LegacyTicketFilterSet;
use JMS\Serializer\Annotation as JMS;

/**
 * Class LegacyTicketFilter.
 */
class LegacyTicketFilter
{
    /**
     * The id of ticket filter.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Display order for filter.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $displayOrder;

    /**
     * System name for this filter.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $sysName;

    /**
     * Original filter`s title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * An array of terms for filter.
     *
     * @JMS\Type("array")
     *
     * @var array
     */
    private $term;

    /**
     * The id of ticket filter set - the owner of ticket filter.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $ticketFilterSet;

    /**
     * (not implemented).
     *
     * @JMS\Type("null")
     *
     * @var null
     */
    private $filterViews;

    /**
     * (not implemented).
     *
     * @JMS\Type("null")
     *
     * @var null
     */
    private $filterPreferences;

    /**
     * (not implemented).
     *
     * @JMS\Type("null")
     *
     * @var null
     */
    private $dateCreated;

    /**
     * (not implemented).
     *
     * @JMS\Type("null")
     *
     * @var null
     */
    private $dateUpdated;

    /**
     * LegacyTicketFilter constructor.
     *
     * @param LegacyTicketFilterEntity $ticketFilter
     * @param LegacyTicketFilterSet    $filterSet
     */
    public function __construct(LegacyTicketFilterEntity $ticketFilter, LegacyTicketFilterSet $filterSet)
    {
        $this->id              = $ticketFilter->getId();
        $this->displayOrder    = $ticketFilter->getDisplayOrder();
        $this->title           = $ticketFilter->getRawTitle();
        $this->sysName         = $ticketFilter->getSysName();
        $this->term            = $ticketFilter->getTerms();
        $this->ticketFilterSet = $filterSet->getId();
    }
}
