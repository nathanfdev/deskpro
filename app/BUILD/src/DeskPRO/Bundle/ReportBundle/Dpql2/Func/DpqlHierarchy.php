<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy\HierarchyPlugin;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Number;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use DeskPRO\Component\Util\ListUtils;

/**
 * Handler for DPQL_HIERARCHY function.
 */
class DpqlHierarchy extends AbstractDpqlFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if ($section != 'group') {
            throw new DpqlException('DPQL_HIERARCHY() may only be used in GROUP BY.');
        }
        if (!in_array(count($arguments), [2, 3])) {
            throw new DpqlException('DPQL_HIERARCHY() can only accept 2 or 3 arguments.');
        }

        $expression = reset($arguments);
        $prepared   = $expression->prepare($statement, $section, $stack, $select, $metadata);

        $minDepth = null;
        if (array_key_exists(1, $arguments)) {
            if (!$arguments[1] instanceof Number) {
                throw new DpqlException('DPQL_HIERARCHY() 2nd argument must be a number.');
            }
            $minDepth = $arguments[1]->getValue();
            if ($minDepth <= 0) {
                throw new DpqlException('DPQL_HIERARCHY() 2nd argument must be a positive number.');
            }
        }

        $maxDepth = null;
        if (array_key_exists(2, $arguments)) {
            if (!$arguments[2] instanceof Number) {
                throw new DpqlException('DPQL_HIERARCHY() 3rd argument must be a number.');
            }
            $maxDepth = $arguments[2]->getValue();
            if ($maxDepth <= 0) {
                throw new DpqlException('DPQL_HIERARCHY() 3rd argument must be a positive number.');
            }
        }

        if ($minDepth && !$maxDepth) {
            $maxDepth = $minDepth;
        }

        if ($minDepth > $maxDepth) {
            throw new DpqlException('DPQL_HIERARCHY() 3rd argument must be bigger or equal than the 2nd.');
        }

        $hierarchyPlugin = $statement->getSqlSelectContext()->getHierarchyPlugin();
        $hierarchyPlugin->setHierarchyMinDepth($minDepth - 1);
        $hierarchyPlugin->setHierarchyMaxDepth($maxDepth - 1);
        $hierarchyPlugin->setForceHierarchy(true);

        $avg = ListUtils::first($select->getSelectFields(), function ($f) {
            return strpos($f, 'AVG(') !== false;
        });

        if ($avg) {
            $hierarchyPlugin->setRollupMode(HierarchyPlugin::ROLLUP_MODE_AVG);
        }

        return $prepared;
    }
}
