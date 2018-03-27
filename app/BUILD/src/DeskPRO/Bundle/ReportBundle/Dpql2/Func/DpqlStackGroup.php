<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler for DPQL_STACK_GROUP function.
 */
class DpqlStackGroup extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 2) {
            throw new DpqlException('DPQL_STACK_GROUP() can only accept 2 arguments.');
        }

        $childStack = $stack;
        array_shift($childStack); // pop this off the stack - it doesn't exist to the children

        /** @var AbstractPart $expression */
        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $childStack, $select, $metadata);

        if ($section == 'group' && !$childStack) {
            /** @var AbstractPart $grouper */
            $grouper      = next($arguments);
            $preppedGroup = $grouper->prepare($statement, $section, $childStack, $select, $metadata);

            if ($preppedGroup->hasValue()) {
                $printId = $statement->addSqlSelectField($preppedGroup->printed());

                if ($preppedGroup->printed() === $preppedGroup->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $statement->addSqlSelectField($preppedGroup->sql());
                }

                $metadata->addGroupStackColumn($groupId, $printId);
            }

            $sql = $prepped->sql();

            return new Prepared($sql, 'DPQL_STACK_GROUP('.$prepped->name().', '.$preppedGroup->name().')', $prepped->printed());
        } else {
            return $prepped;
        }
    }
}
