<?php

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
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFilterTerm>")
     *
     * @var array
     */
    private $term = [];

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
        $this->ticketFilterSet = $filterSet->getId();

        foreach ($ticketFilter->getTerms() as $term) {
            $this->term[] = new TicketFilterTerm($term);
        }
    }
}
