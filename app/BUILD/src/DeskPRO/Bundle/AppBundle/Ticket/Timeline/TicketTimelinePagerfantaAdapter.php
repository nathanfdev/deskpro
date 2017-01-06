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
