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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\AppBundle\Dpql2\Placeholder\DpqlPlaceholderRegistry;
use DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler;
use DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart;

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
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultHandler $result)
    {
        $prepared = $this->dpqlPlaceholderRegistry->getPlaceholder($this->name)->prepare(
            $statement, $section, $stack, $select, $result
        );
        $prepared->setName('%'.$this->name.'%');

        return $prepared;
    }

    /**
     * Prepares a part for use, including validating that the usage is valid.
     *
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart            $statement
     * @param string                                                          $section   Name of the section usage is in (select, where, split, group, order)
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]   $stack     Parent parts
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect                       $select    Select being built up
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler                   $result
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\BinaryInterval[] $intervals List of intervals that affect this calculation
     *
     * @throws \DeskPRO\Bundle\AppBundle\Dpql2\Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\Prepared|bool Prepared results or false if there's no output
     */
    public function prepareWithIntervals(
        SelectPart $statement, $section, array $stack, SqlSelect $select, ResultHandler $result, array $intervals = []
    ) {
        $prepared = $this->dpqlPlaceholderRegistry->getPlaceholder($this->name)->prepareWithIntervals(
            $statement, $section, $stack, $select, $result, $intervals
        );

        $append = '';
        foreach ($intervals as $interval) {
            $operator = $interval->operator == \DeskPRO\Bundle\AppBundle\Dpql2\Parser::T_OP_PLUS ? '+' : '-';
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
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart   $lhs        The left hand side of the comparison
     * @param string                                                        $comparison The comparison operator
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\SelectPart          $statement
     * @param string                                                        $section    Name of the section usage is in (select, where, split, group, order)
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[] $stack      Parent parts
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect                     $select
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler                 $result
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\BinaryInterval[] List of intervals that affect this calculation
     *
     * @throws \DeskPRO\Bundle\AppBundle\Dpql2\Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\Prepared|bool Prepared results or false the default behavior should be called
     */
    public function prepareComparison(
        AbstractPart $lhs, $comparison, SelectPart $statement, $section, array $stack,
        SqlSelect $select, ResultHandler $result, array $intervals = []
    ) {
        return $this->dpqlPlaceholderRegistry->getPlaceholder($this->name)->prepareComparison(
            $lhs, $comparison, $statement, $section, $stack, $select, $result, $intervals
        );
    }
}
