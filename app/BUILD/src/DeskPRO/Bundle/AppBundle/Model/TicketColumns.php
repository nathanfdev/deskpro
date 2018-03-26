<?php

namespace DeskPRO\Bundle\AppBundle\Model;

class TicketColumns implements \IteratorAggregate, \Countable
{
    /**
     * @var TicketColumn[]
     */
    protected $columns;

    public function __construct()
    {
        $this->columns = [];
    }

    public function toArray()
    {
        return array_map(function ($col) {
            /* @var TicketColumn $col */
            return $col->toArray();
        }, $this->columns);
    }

    public function addColumn($id, $label, $type)
    {
        $this->appendColumn(new TicketColumn($id, $label, $type));
    }

    public function appendColumn(TicketColumn $ticket_column)
    {
        $this->columns[$ticket_column->getId()] = $ticket_column;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->columns);
    }

    public function count()
    {
        return count($this->columns);
    }
}
