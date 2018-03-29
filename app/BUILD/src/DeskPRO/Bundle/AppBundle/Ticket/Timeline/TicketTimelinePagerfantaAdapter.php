<?php

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * Use this adapter for paging over a TicketTimeline.
 */
class TicketTimelinePagerfantaAdapter implements AdapterInterface
{
    /**
     * @var TicketTimeline
     */
    private $ticket_timeline;

    /**
     * @param TicketTimeline $ticket_timeline
     */
    public function __construct(TicketTimeline $ticket_timeline)
    {
        $this->ticket_timeline = $ticket_timeline;
    }

    /**
     * Returns the timeline.
     *
     * @return TicketTimeline
     */
    public function getTimeline()
    {
        return $this->ticket_timeline;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $lines = $this->ticket_timeline->getTotalLinesForPager();

        return $lines;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        // the timeline should be constructed with the total NbResults int, but only add
        // the appropriate lines for this page to the timeline using addLine().
        return $this->ticket_timeline;
    }
}
