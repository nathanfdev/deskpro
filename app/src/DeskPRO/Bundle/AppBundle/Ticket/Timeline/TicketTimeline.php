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

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline;

use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\LineInterface;

class TicketTimeline implements \IteratorAggregate
{
    /**
     * @var LineInterface[]
     */
    protected $lines;

    public function __construct()
    {
        $this->lines = [];
    }

    /**
     * @param LineInterface $line
     */
    public function addLine(LineInterface $line)
    {
        $this->lines[] = $line;
    }

    /**
     * This logic is used to determine if we display a <hr> in between messages.
     *
     * @param LineInterface $line
     *
     * @return bool
     */
    public function isMultipleUserActions(LineInterface $line)
    {
        if ($this->isLineByAnAgent($line)) {
            return false;
        }

        if ($next_line = $this->getNextLine($line)) {
            // this line is a user line AND the next one is too
            return !$this->isLineByAnAgent($next_line);
        }

        return false;
    }

    /**
     * @param $next_line
     *
     * @return bool
     */
    protected function isLineByAnAgent(LineInterface $next_line)
    {
        return $next_line->getPerson() && $next_line->getPerson()->isAgent();
    }

    /**
     * @param LineInterface $before_line
     *
     * @return LineInterface|void
     */
    protected function getNextLine(LineInterface $before_line)
    {
        $next = false;

        foreach ($this->lines as $line) {
            if ($next) {
                // return the first line after the iterator would find $before_line
                return $line;
            }

            // using object identitiy to check
            if ($before_line === $line) {
                $next = true;
            }
        }

        return;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->lines);
    }
}
