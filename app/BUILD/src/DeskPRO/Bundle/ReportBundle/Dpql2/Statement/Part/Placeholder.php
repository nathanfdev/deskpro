<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\DpqlPlaceholderRegistry;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a placeholder reference.
 */
class Placeholder extends AbstractPart
{
    /**
     * @var DpqlPlaceholderRegistry
     */
    private $dpqlPlaceholderRegistry;

    /**
     * @var string
     */
    public $name;

    /**
     * Constructor.
     *
     * @param DpqlPlaceholderRegistry $dpqlPlaceholderRegistry
     * @param string                  $name
     */
    public function __construct(DpqlPlaceholderRegistry $dpqlPlaceholderRegistry, $name)
    {
        $this->dpqlPlaceholderRegistry = $dpqlPlaceholderRegistry;
        $this->name                    = $name;
    }

    /**
     * @throws DpqlException
     *
     * @return \DeskPRO\Bundle\ReportBundle\Dpql2\Placeholder\AbstractPlaceholder
     */
    public function getPlaceholder()
    {
        return $this->dpqlPlaceholderRegistry->getPlaceholder($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $prepared = $this->dpqlPlaceholderRegistry->getPlaceholder($this->name)->prepare(
            $statement, $section, $stack, $select, $metadata
        );
        $prepared->setName('%'.$this->name.'%');

        return $prepared;
    }

    /**
     * Prepares a part for use, including validating that the usage is valid.
     *
     * @param SelectPart       $statement
     * @param string           $section   Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[]   $stack     Parent parts
     * @param SqlSelect        $select    Select being built up
     * @param ResultMetadata   $result
     * @param BinaryInterval[] $intervals List of intervals that affect this calculation
     *
     * @throws DpqlException
     *
     * @return Prepared|bool Prepared results or false if there's no output
     */
    public function prepareWithIntervals(
        SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result, array $intervals = []
    ) {
        $prepared = $this->dpqlPlaceholderRegistry->getPlaceholder($this->name)->prepareWithIntervals(
            $statement, $section, $stack, $select, $result, $intervals
        );

        $append = '';
        foreach ($intervals as $interval) {
            $operator = $interval->operator == \DeskPRO\Bundle\ReportBundle\Dpql2\Parser::T_OP_PLUS ? '+' : '-';
            $append .= " $operator INTERVAL $interval->amount $interval->unit";
        }

        $prepared->setName('%'.$this->name.'%'.$append);

        return $prepared;
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return '%'.$this->name.'%';
    }

    /**
     * Prepares the placeholder when it's called in a binary comparison context.
     * The placeholder is always the right hand side of the comparison. This is
     * needed for placeholders that actually map to date ranges rather than scalars.
     *
     * @param AbstractPart     $lhs        The left hand side of the comparison
     * @param string           $comparison The comparison operator
     * @param SelectPart       $statement
     * @param string           $section    Name of the section usage is in (select, where, split, group, order)
     * @param AbstractPart[]   $stack      Parent parts
     * @param SqlSelect        $select
     * @param ResultMetadata   $result
     * @param BinaryInterval[] $intervals  List of intervals that affect this calculation
     *
     * @throws DpqlException
     *
     * @return Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultMetadata $result, array $intervals = []
    ) {
        return $this->dpqlPlaceholderRegistry->getPlaceholder($this->name)->prepareComparison(
            $lhs, $comparison, $statement, $section, $stack, $select, $result, $intervals
        );
    }
}
