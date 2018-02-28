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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Gets the percentage of all rows in the group that match the given argument.
 * For example, DPQL_PERCENT(table.column > 10).
 */
class DpqlPercent extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (!in_array($section, ['select', 'split', 'group', 'order'])) {
            throw new DpqlException('DPQL_PERCENT() may only be used in SELECT, SPLIT BY, GROUP BY, and ORDER BY sections.');
        }

        if (!in_array(count($arguments), [1, 2])) {
            throw new DpqlException('DPQL_PERCENT() can only accept 1 or 2 arguments');
        }

        $condition = reset($arguments);
        $prepped   = $condition->prepare($statement, $section, $stack, $select, $metadata);

        $sql = 'IF(COUNT(*) > 0, (SUM(IF('.$prepped->sql().', 1, 0)) / COUNT(*)) * 100, 0)';

        return new Prepared($sql, 'DPQL_PERCENT('.$prepped->name().')', false, 'percent');
    }
}
