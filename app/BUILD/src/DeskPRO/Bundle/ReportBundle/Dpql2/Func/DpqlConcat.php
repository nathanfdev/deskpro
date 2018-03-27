<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class DpqlConcat.
 */
class DpqlConcat extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) < 2) {
            throw new DpqlException('DPQL_CONCAT() requires at least 2 arguments.');
        }
        if ($section !== 'select') {
            throw new DpqlException('DPQL_CONCAT() may only be used in SELECT.');
        }

        $argNames = [];
        $preppeds = [];
        foreach ($arguments as $argument) {
            $prepped    = $argument->prepare($statement, $section, $stack, $select, $metadata);
            $argNames[] = $prepped->name();
            $preppeds[] = $prepped;
        }

        $name     = implode(',', $argNames);
        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($preppeds) {
            $result = '';
            foreach ($value as $key => $item) {
                $itemRenderer = $preppeds[$key]->renderer();
                if ($itemRenderer instanceof \Closure) {
                    $result .= $itemRenderer($valueRenderer, $item, $row, $renderer, $metadata);
                } else {
                    $result .= $valueRenderer->renderValue($item, $itemRenderer, $metadata);
                }
            }

            return $result;
        };

        return new Prepared(null, 'DPQL_CONCAT('.$name.')', false, $renderer);
    }
}
