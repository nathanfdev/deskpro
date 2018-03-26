<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Statement\Display;

/**
 * Handler for HIERARCHY_DESCENDS_FROM function.
 */
class HierarchyDescendsFrom extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(
        Display $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
    ) {
        if ($section != 'where') {
            throw new Exception('HIERARCHY_DESCENDS_FROM() may only be used in WHERE.');
        }
        if (count($this->_arguments) !== 2) {
            throw new Exception('HIERARCHY_DESCENDS_FROM() must have 2 arguments.');
        }
        if (!$this->_arguments[1] instanceof Dpql\Statement\Part\Number) {
            throw new Exception('HIERARCHY_DESCENDS_FROM() 2nd argument must be a number.');
        }

        $expression    = $this->_arguments[0];
        $expressionSql = $expression->prepare($statement, $section, $stack, $select, $result)->sql();

        if (!preg_match('/`(.+)`\.`.+`/isU', $expressionSql, $matches)) {
            throw new Exception('HIERARCHY_DESCENDS_FROM cannot resolve the target table alias');
        }
        $targetTableAlias = $matches[1];
        $targetTableName  = null;

        $joins = $select->getJoins();
        foreach ($joins as $joinAlias => $joinSql) {
            if ($joinAlias === $targetTableAlias) {
                if (!preg_match("/LEFT JOIN `(.+)` AS `$targetTableAlias`.+/isU", $joinSql, $matches)) {
                    throw new Exception('HIERARCHY_DESCENDS_FROM cannot resolve the target table name');
                }
                $targetTableName = $matches[1];
            }
        }
        $targetTableName or $targetTableName = $select->getTable();

        $hierarchyPlugin = $statement->getSqlSelectContext()->getHierarchyPlugin();
        $hierarchyPlugin->setHierarchyDescendsFrom($this->_arguments[1]->getValue(), $targetTableName);
        $ids = $hierarchyPlugin->collectChildrenIds();
        $ids = array_map(function ($num) {
            return new Dpql\Statement\Part\Number($num);
        }, $ids);

        $condition = new Dpql\Statement\Part\In(
            new Dpql\Statement\Part\Raw("`$targetTableAlias`.`id`"),
            $ids
        );
        $prepared = $condition->prepare($statement, $section, $stack, $select, $result);

        $result->addFlag(Dpql\ResultHandler::FLAG_HIERARCHY_DESCENDS_FROM);

        return $prepared;
    }
}
