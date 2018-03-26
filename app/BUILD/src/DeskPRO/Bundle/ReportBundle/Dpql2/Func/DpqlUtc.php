<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Ensures that all date references within this are not adjusted for the
 * current user's time zone. This can be used to increase performance.
 */
class DpqlUtc extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) != 1) {
            throw new DpqlException('DPQL_UTC() can only accept 1 argument');
        }

        $arg = reset($arguments);

        $prepared = $arg->prepare($statement, $section, $stack, $select, $metadata);
        $prepared->setName('DPQL_UTC('.$prepared->name().')');

        return $prepared;
    }
}
