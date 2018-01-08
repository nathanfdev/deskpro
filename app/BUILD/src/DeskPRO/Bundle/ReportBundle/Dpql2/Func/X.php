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
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\NullValue;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Used in the GROUP BY clause to specify columns that will be used
 * in the X direction to create a matrix table.
 */
class X extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if ($section != 'group') {
            throw new DpqlException('X() may only be used in GROUP BY.');
        }
        if (count($stack) > 1) {
            // note: the top of the stack is this function
            throw new DpqlException('X() may only be used at the top-level.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        foreach ($arguments as $arg) {
            if ($arg instanceof NullValue) {
                continue;
            }

            $groupBy = $arg->prepare($statement, $section, $childStack, $select, $metadata);
            if ($groupBy->hasValue()) {
                $printId = $select->addSelectField($groupBy->printed());
                $select->addGroupBy($groupBy->sql());
                $defaultOrder = $statement->addDefaultOrder($groupBy->printed());

                if ($groupBy->printed() === $groupBy->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $select->addSelectField($groupBy->sql());
                }

                if ($groupBy->ordered() == $groupBy->printed()) {
                    $orderId = $printId;
                } else {
                    $orderId = $select->addSelectField($groupBy->ordered());
                }

                if ($defaultOrder && $groupBy->groupFill()) {
                    $statement->addGroupFill($groupBy->groupFill(), $printId, $groupId, $orderId);
                }

                $metadata->addGroupXColumn($groupBy->name(), $groupId, $printId, $groupBy->renderer());
            }
        }

        return new Prepared(false);
    }
}
