<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents an alias (X AS Y) part of a DPQL statement.
 */
class Alias extends AbstractPart
{
    /**
     * @var AbstractPart
     */
    public $value;

    /**
     * @var string
     */
    public $alias;

    /**
     * Constructor.
     *
     * @param SelectPart|AbstractPart $value
     * @param string                  $alias
     */
    public function __construct($value, $alias)
    {
        $this->value = $value;
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        if ($section !== 'from') {
            throw new DpqlException('Alias prepare() cannot not be called');
        }

        if ($this->value instanceof SelectPart) {
            $this->value->prepare();

            return new Prepared('('.$this->value->toSql().')', $this->alias);
        } else {
            $childStack = $this->getChildStack($stack);
            $value      = $this->value->prepare($statement, $section, $childStack, $select, $result);

            return new Prepared('('.$value->sql().')', $this->alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        return $this->value->toDpql($statement, $section, $stack).' AS '.$statement->quoteDpqlString($this->alias);
    }
}
