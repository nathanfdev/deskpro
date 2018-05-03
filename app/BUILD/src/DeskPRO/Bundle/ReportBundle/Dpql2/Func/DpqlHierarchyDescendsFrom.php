<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Helper\CustomDataHelper;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\DpqlStatementFactory;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Number;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Variable;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handler for DPQL_HIERARCHY_DESCENDS_FROM function.
 */
class DpqlHierarchyDescendsFrom extends AbstractDpqlFunc
{
    /**
     * @var DpqlStatementFactory
     */
    private $statementFactory;

    /**
     * @var CustomDataHelper
     */
    private $custoDataHelper;

    /**
     * Constructor.
     *
     * @param DpqlStatementFactory $statementFactory
     * @param CustomDataHelper     $customDataHelper
     */
    public function __construct(DpqlStatementFactory $statementFactory, CustomDataHelper $customDataHelper)
    {
        $this->statementFactory = $statementFactory;
        $this->custoDataHelper  = $customDataHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if ($section != 'where') {
            throw new DpqlException('DPQL_HIERARCHY_DESCENDS_FROM() may only be used in WHERE.');
        }
        if (count($arguments) !== 2) {
            throw new DpqlException('DPQL_HIERARCHY_DESCENDS_FROM() must have 2 arguments.');
        }
        if (!$arguments[1] instanceof Number && !$arguments[1] instanceof Variable && !$arguments[1] instanceof StringPart) {
            throw new DpqlException('DPQL_HIERARCHY_DESCENDS_FROM() 2nd argument must be a number or variable.');
        }

        $expression    = $arguments[0];
        $expressionSql = $expression->prepare($statement, $section, $stack, $select, $metadata)->sql();

        if (!preg_match('/`(.+)`\.`.+`/isU', $expressionSql, $matches)) {
            throw new DpqlException('DPQL_HIERARCHY_DESCENDS_FROM cannot resolve the target table alias');
        }
        $targetTableAlias = $matches[1];
        $targetTableName  = null;

        $joins = $select->getJoins();
        foreach ($joins as $joinAlias => $joinSql) {
            if ($joinAlias === $targetTableAlias) {
                if (!preg_match("/LEFT JOIN `(.+)` AS `$targetTableAlias`.+/isU", $joinSql, $matches)) {
                    throw new DpqlException('DPQL_HIERARCHY_DESCENDS_FROM cannot resolve the target table name');
                }
                $targetTableName = $matches[1];
            }
        }
        $targetTableName or $targetTableName = $select->getTable();

        // this is simple workaround for string vars which custom_data vars are
        // special condition to find descendants only would be applied
        $customDataHierarchy = strpos($targetTableName, 'custom_data_') !== false;
        if ($customDataHierarchy) {
            $targetTableAlias .= '_field';
            $targetTableName = $this->custoDataHelper->getDefTable($targetTableName);
        }

        $hierarchyPlugin = $statement->getSqlSelectContext()->getHierarchyPlugin();
        $hierarchyPlugin->setHierarchyDescendsFrom($arguments[1]->getValue(), $targetTableName);
        $ids = $hierarchyPlugin->collectChildrenIds();
        $ids = array_map(function ($num) {
            return new Number($num);
        }, $ids);

        if (empty($ids)) {
            $ids = [new Number(0)];
        }

        $condition = $this->statementFactory->createIn(
            $this->statementFactory->createRaw("`$targetTableAlias`.`id`"),
            $ids
        );
        $prepared = $condition->prepare($statement, $section, $stack, $select, $metadata);

        $metadata->addFlag(ResultMetadata::FLAG_HIERARCHY_DESCENDS_FROM);

        return $prepared;
    }
}
