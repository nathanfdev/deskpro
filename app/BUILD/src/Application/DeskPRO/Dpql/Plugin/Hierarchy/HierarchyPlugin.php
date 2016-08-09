<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\Dpql\Plugin\Hierarchy;

use Application\DeskPRO\Dpql\Plugin\PluginInterface;
use Application\DeskPRO\Dpql\ResultHandler;
use Application\DeskPRO\Dpql\SqlSelect;
use Application\DeskPRO\Dpql\Statement\Part\Column;

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
        list($id, $title)        = Column::resolveTable(Hierarchy::getGroupingTargetTable($this->sql));
        $this->sql->addSelectField("`$hierarchicalTargetTable`.`$id` as 'hierarchy_id'");
        $this->sql->addSelectField("`$hierarchicalTargetTable`.`parent_id` as 'hierarchy_parent_id'");
        $this->sql->addSelectField("`$hierarchicalTargetTable`.`$title` as 'hierarchy_title'");
    }

    /**
     * {@inheritdoc}
     */
    public function afterQuery(array $results)
    {
        if (!Hierarchy::isHierarchical($this->sql) || empty($results)) {
            return $results;
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
            $result['hierarchy_id']        = $result[$idIndex];
            $result['hierarchy_parent_id'] = $result[$parentIdIndex];
            $result['hierarchy_title']     = $result[$titleIndex];
            unset($result[$idIndex]);
            unset($result[$parentIdIndex]);
            unset($result[$titleIndex]);
        }

        // Tree sort and count depth
        $results = HierarchySorting::sort(
            $results,
            Hierarchy::getGroupingTargetTable($this->sql),
            Hierarchy::getGroupingTargetTableReference($this->sql),
            $this->sql->getSelectFields()
        );

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

        $handler->addGroupXColumn('', 'hierarchy_root_title', 2); // in count queries 2 refers to COUNT() result
        $handler->addFlag(ResultHandler::FLAG_GROUP_ONLY_CHART);
    }
}
