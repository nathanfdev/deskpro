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
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler for STACK_GROUP function.
 */
class StackGroup extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $result)
    {
        if (count($arguments) != 2) {
            throw new DpqlException('STACK_GROUP() can only accept 2 arguments.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        /** @var AbstractPart $expression */
        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $childStack, $select, $result);

        if ($section == 'group' && !$childStack) {
            /** @var AbstractPart $grouper */
            $grouper      = next($arguments);
            $preppedGroup = $grouper->prepare($statement, $section, $childStack, $select, $result);

            if ($preppedGroup->hasValue()) {
                $printId = $statement->addSqlSelectField($preppedGroup->printed());

                if ($preppedGroup->printed() === $preppedGroup->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $statement->addSqlSelectField($preppedGroup->sql());
                }

                $result->addGroupStackColumn($groupId, $printId);
            }

            $sql = $prepped->sql();

            return new Prepared($sql, 'STACK_GROUP('.$prepped->name().', '.$preppedGroup->name().')', $prepped->printed());
        } else {
            return $prepped;
        }
    }
}
