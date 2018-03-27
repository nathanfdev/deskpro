<?php

namespace DeskPRO\Bundle\AppBundle\Model;

/**
 * Class TicketColumns.
 */
class TicketColumns implements \IteratorAggregate, \Countable
{
    /**
     * @var TicketColumn[]
     */
    protected $columns;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->columns = [];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($col) {
            /* @var TicketColumn $col */
            return $col->toArray();
        }, $this->columns);
    }

    /**
     * @param string $id
     * @param string $label
     * @param string $type
     * @param string $widgetType
     */
    public function addColumn($id, $label, $type, $widgetType)
    {
        $this->appendColumn(new TicketColumn($id, $label, $type, $widgetType));
    }

    /**
     * @param TicketColumn $ticketColumn
     */
    public function appendColumn(TicketColumn $ticketColumn)
    {
        $this->columns[$ticketColumn->getId()] = $ticketColumn;
    }

    /**
     * @return array|TicketColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->columns);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->columns);
    }
}
