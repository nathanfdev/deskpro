<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents an order and direction (ASC, DESC).
 */
class OrderDir extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $order;

    /**
     * ASC or DESC.
     *
     * @var string
     */
    public $orderDir;

    /**
     * Constructor.
     *
     * @param AbstractPart $order
     * @param string       $orderDir
     */
    public function __construct(AbstractPart $order, $orderDir)
    {
        $this->order    = $order;
        $this->orderDir = $orderDir;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        throw new DpqlException('Order direction prepare() cannot not be called');
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->order->toDpql($statement, $section, $stack).' '.$this->orderDir;
    }
}
