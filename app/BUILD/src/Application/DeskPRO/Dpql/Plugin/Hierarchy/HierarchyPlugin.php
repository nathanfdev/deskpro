<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Plugin\Hierarchy;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Dpql\Plugin\PluginInterface;
use Application\DeskPRO\Dpql\ResultHandler;
use Application\DeskPRO\Dpql\SqlSelect;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Dpql\Statement\Part\Column;
use Application\DeskPRO\Entity\Hierarchy\Hierarchical;

/**
 * Class HierarchyPlugin.
 */
class HierarchyPlugin implements PluginInterface
{
    /**
     * @var SqlSelect
     */
    private $sql;

    /**
     * @var Display
     */
    private $display;

    /**
     * @var int Zero-indexed
     */
    private $hierarchyMinDepth = 0;

    /**
     * @var int|null Zero-indexed
     */
    private $hierarchyMaxDepth = null;

    /**
     * @var int|null
     */
    private $hierarchyDescendsFromId = null;

    /**
     * @var string|null
     */
    private $hierarchyDescendsFromTable = null;

    /**
     * @var string|null
     */
    private $titleFieldSql = null;

    /**
     * @param Display $display
     */
    public function __construct(Display $display)
    {
        $this->display = $display;
    }

    /**
     * @param SqlSelect $sql
     */
    public function provide(SqlSelect $sql)
    {
        $this->sql = $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeQuery()
    {
        if (!Hierarchy::isHierarchical($this->sql)) {
            return;
        }
        $hierarchicalTargetTable = Hierarchy::getGroupingTargetTableReference($this->sql);
        $groupingTableName       = Hierarchy::getGroupingTargetTable($this->sql);
        list($id, $title)        = Column::resolveTable($groupingTableName);

        if (strpos($groupingTableName, 'custom_def') === 0) {
            $selectHierarchicalTargetTable = $hierarchicalTargetTable;
            if (!preg_match('/_field$/', $selectHierarchicalTargetTable)) {
                $selectHierarchicalTargetTable .= '_field';
            }

            $this->sql->addSelectField("`{$selectHierarchicalTargetTable}`.`$id` as 'hierarchy_id'");
            $this->sql->addSelectField("`{$selectHierarchicalTargetTable}`.`options` as 'hierarchy_parent_options'");
            $this->sql->addSelectField("`{$selectHierarchicalTargetTable}`.`$title` as 'hierarchy_title'");

            $this->titleFieldSql = "`{$selectHierarchicalTargetTable}`.`$title`";
        } else {
            $this->sql->addSelectField("`$hierarchicalTargetTable`.`$id` as 'hierarchy_id'");
            $this->sql->addSelectField("`$hierarchicalTargetTable`.`parent_id` as 'hierarchy_parent_id'");
            $this->sql->addSelectField("`$hierarchicalTargetTable`.`$title` as 'hierarchy_title'");

            $this->titleFieldSql = "`$hierarchicalTargetTable`.`$title`";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterQuery(array $results)
    {
        if (!Hierarchy::isHierarchical($this->sql) || empty($results)) {
            return $results;
        }

        // Load parents name path if HIERARCHY_DESCENDS_FROM was used
        $path = '';
        if ($this->hierarchyDescendsFromId && count($results)) {
            list($id, $title) = Column::resolveTable($this->hierarchyDescendsFromTable);
            $sql              =
                "SELECT `{$title}`, `parent_id` FROM `{$this->hierarchyDescendsFromTable}` WHERE `{$id}` = ?";
            $parentsLimit = 10;
            $parentId     = $this->hierarchyDescendsFromId;
            while ($parentsLimit > 0) {
                $entries = App::getDbRead('reports')->executeQuery($sql, [$parentId])->fetchAll(\PDO::FETCH_NUM);
                if (count($entries)) {
                    $path .= "{$entries[0][0]} > ";
                    if (!$parentId = $entries[0][1]) {
                        break;
                    }
                }
                --$parentsLimit;
            }
        }

        // Turn last three numeric fields to meta data
        $lastNum = 1;
        while (array_key_exists($lastNum, $results[0])) {
            ++$lastNum;
        }
        $titleIndex    = $lastNum - 1;
        $parentIdIndex = $lastNum - 2;
        $idIndex       = $lastNum - 3;
        foreach ($results as &$result) {
            $result['hierarchy_id']    = $result[$idIndex];
            $result['hierarchy_title'] = $path.$result[$titleIndex];

            if (strpos($this->titleFieldSql, 'custom_data_') !== false) {
                $customFieldOptions = $result[$parentIdIndex];
                $decodedOptions     = @unserialize($customFieldOptions);

                if (isset($decodedOptions['parent_id'])) {
                    $result['hierarchy_parent_id'] = $decodedOptions['parent_id'];
                } else {
                    $result['hierarchy_parent_id'] = null;
                }
            } else {
                $result['hierarchy_parent_id'] = $result[$parentIdIndex];
            }

            unset($result[$idIndex]);
            unset($result[$parentIdIndex]);
            unset($result[$titleIndex]);
        }

        // Tree sort and count depth
        $countFieldNum = $this->getCountFieldNum();
        $results       = HierarchySorting::sort(
            $results,
            Hierarchy::getGroupingTargetTable($this->sql),
            Hierarchy::getGroupingTargetTableReference($this->sql),
            $this->sql->getSelectFields(),
            $countFieldNum
        );

        // Replace table entry name/title with hierarchy_title
        if (!is_null($titleFieldNum = $this->getTitleFieldNum())) {
            foreach ($results as &$result) {
                if (array_key_exists('hierarchy_title', $result)) {
                    $result[$titleFieldNum] = $result['hierarchy_title'];
                }
            }
        }

        // Init rollup counts if needed
        if ($this->display->withRollup() && !is_null($countFieldNum)) {
            $results = HierarchyRollup::init($results);
        }

        // Limit depth if needed
        if ($this->hierarchyMinDepth > 0 || !is_null($this->hierarchyMaxDepth)) {
            $results = HierarchyDepth::limitTo($this->hierarchyMinDepth, $this->hierarchyMaxDepth, $results);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function resultHandlerCallback(ResultHandler $handler, array $results)
    {
        if (!Hierarchy::isHierarchical($this->sql)) {
            return;
        }

        $countFieldNum = $this->getCountFieldNum();
        $handler->addGroupXColumn('', 'hierarchy_root_title', $countFieldNum ? $countFieldNum + 1 : 1);
        $handler->addFlag(ResultHandler::FLAG_HIERARCHICAL);
    }

    /**
     * @return int
     */
    public function getHierarchyMinDepth()
    {
        return $this->hierarchyMinDepth;
    }

    /**
     * @param int $hierarchyMinDepth
     */
    public function setHierarchyMinDepth($hierarchyMinDepth)
    {
        $this->hierarchyMinDepth = $hierarchyMinDepth;
    }

    /**
     * @return int|null
     */
    public function getHierarchyMaxDepth()
    {
        return $this->hierarchyMaxDepth;
    }

    /**
     * @param int|null $hierarchyMaxDepth
     */
    public function setHierarchyMaxDepth($hierarchyMaxDepth)
    {
        $this->hierarchyMaxDepth = $hierarchyMaxDepth;
    }

    /**
     * @param int    $id
     * @param string $table
     */
    public function setHierarchyDescendsFrom($id, $table)
    {
        $this->hierarchyDescendsFromId    = $id;
        $this->hierarchyDescendsFromTable = $table;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function collectChildrenIds()
    {
        $rootId     = $this->hierarchyDescendsFromId;
        $repository = Display::getRepositoryByTable($this->hierarchyDescendsFromTable);

        /** @var Hierarchical $root */
        if (!$root = $repository->find($rootId)) {
            return [];
        }
        if (!$root instanceof Hierarchical) {
            throw new Exception("{$this->hierarchyDescendsFromTable} is not a Hierarchical entity");
        }

        $children                        = $root->getChildren();
        is_array($children) or $children = $children->toArray();
        $children                        = array_map(function (Hierarchical $entity) {
            return $entity->getId();
        }, $children);

        return $children;
    }

    /**
     * @return int|null
     */
    private function getCountFieldNum()
    {
        $fields = $this->sql->getSelectFields();
        foreach ($fields as $i => $field) {
            if (!is_int($i)) {
                continue;
            }

            if (strpos($field, 'COUNT(') === 0) {
                return $i;
            }

            // Some COUNT queries are compiled into SUM(IF(`some_field`, 1, 0))
            if ((strpos($field, 'SUM(IF(`') === 0) && (strrpos($field, ', 1, 0))') === strlen($field) - 8)) {
                return $i;
            }
        }

        return;
    }

    /**
     * @return int|null
     */
    private function getTitleFieldNum()
    {
        foreach ($this->sql->getSelectFields() as $i => $field) {
            if ($field === $this->titleFieldSql) {
                return $i;
            }
        }

        return;
    }
}
