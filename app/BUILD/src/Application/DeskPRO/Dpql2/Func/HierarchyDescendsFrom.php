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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql2\Func;

use Application\DeskPRO\Dpql;
use Application\DeskPRO\Dpql2\Exception;
use Application\DeskPRO\Dpql2\Statement\SelectPart;

/**
 * Handler for HIERARCHY_DESCENDS_FROM function.
 */
class HierarchyDescendsFrom extends AbstractFunc
{
    /**
     * {@inheritdoc}
     */
    public function prepare(
        SelectPart $statement, $section, array $stack, Dpql\SqlSelect $select, Dpql\ResultHandler $result
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
