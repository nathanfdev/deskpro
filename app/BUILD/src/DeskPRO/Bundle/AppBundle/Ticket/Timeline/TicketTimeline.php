<?php

namespace DeskPRO\Bundle\AppBundle\Ticket\Timeline;

use DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\LineInterface;

class TicketTimeline implements \IteratorAggregate
{
    /**
     * @var LineInterface[]
     */
    protected $lines;

    /**
     * @var int|null
     */
    private $total_lines;

    /**
     * A tactic we use to paginate a TicketTimeline is to only use addLine for the lines we want to display on the current
     * page. This means counting $lines will not give you the total # of lines that would be present with no pagination.
     * You should pass $total_lines into the constructor if you are using a pager, so that the pagerfanta adapter can
     * know the total nb of lines.
     *
     * @param int|null $total_lines
     */
    public function __construct($total_lines = null)
    {
        $this->lines       = [];
        $this->total_lines = (int) $total_lines;
    }

    public function getTotalLinesForPager()
    {
        return $this->total_lines !== null ? $this->total_lines : count($this->lines);
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
