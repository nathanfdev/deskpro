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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class JsonBarRenderer.
 */
class JsonBarRenderer extends AbstractJsonChartRenderer
{
    /**
     * @var array
     */
    protected $options = [
        'type'       => 'column',
        'fillAlphas' => 1,
    ];

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_BAR;
    }

    /**
     * {@inheritdoc}
     */
    public function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return;
        }

        $groupXColumns = $metadata->getGroupXColumns();
        $groupYColumns = $metadata->getGroupYColumns();
        $selectColumns = $metadata->getSelectColumns();

        $arrayOutput = $this->getDefaultOutputArray();
        $chartData   = $graphs   = [];

        // we have stacked results here
        if ($groupXColumns && $metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)) {
            $arrayOutput['valueAxes'][0]['stackType'] = 'regular';
            $arrayOutput['valueAxes'][0]['title']     = $selectColumns[0]['title'];
            $arrayOutput['categoryAxis']['title']     = $groupYColumns[0]['title'];

            $hierarchyParents = $this->collectHierarchyParents($rows);
            $stacks           = $this->getStacks($rows, $hierarchyParents);

            foreach ($stacks as $i => $stack) {
                $chartData[$i] = [];
                foreach ($stack as $j => $values) {
                    $chartData[$i]['category']        = isset($hierarchyParents[$i]) ? $hierarchyParents[$i]['hierarchy_root_title'] : $values['hierarchy_root_title'];
                    $chartData[$i]["value$i$j"]       = $values[$selectColumns[0]['resultId'] - 1];
                    $chartData[$i]["title_value$i$j"] = $values['hierarchy_title'];
                    $graphs[]                         = [
                        'id'          => "graph-$i-$j",
                        'type'        => 'column',
                        'fillAlphas'  => true,
                        'valueField'  => "value$i$j",
                        'title'       => $values['hierarchy_title'],
                        'balloonText' => '[[title]]:[[value]]',
                    ];
                }
            }

            $arrayOutput['dataProvider'] = array_values($chartData);
            $arrayOutput['graphs']       = array_values($graphs);

            return $arrayOutput;
        }

        return parent::doRender($rows, $metadata, $options);
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    protected function collectHierarchyParents(array &$rows)
    {
        $hierarchyParents = [];
        foreach ($rows as $row) {
            if ($row['hierarchy_parent_id']) {
                $parent = $this->findHierarchyParent($rows, $row['hierarchy_parent_id']);
                if ($parent) {
                    list($index, $parent) = $parent;
                    // collect all hierarchy parents, so we gonna stack results under them
                    $hierarchyParents[$parent['hierarchy_id']] = $parent;
                    unset($rows[$index]);
                }
            }
        }

        return $hierarchyParents;
    }

    /**
     * @param array $rows
     * @param int   $parentId
     *
     * @return array|bool
     */
    protected function findHierarchyParent($rows, $parentId)
    {
        foreach ($rows as $i => $row) {
            if ($row['hierarchy_id'] === $parentId) {
                return [$i, $row];
            }
        }

        return false;
    }

    /**
     * @param array $rows
     * @param array $hierarchyParents
     *
     * @return array
     */
    protected function getStacks(array &$rows, array $hierarchyParents)
    {
        $stacks = [];
        foreach ($rows as $index => $row) {
            $parentId = $row['hierarchy_parent_id'];
            if (array_key_exists($parentId, $hierarchyParents)) {
                if (!isset($stacks[$parentId]) || !is_array($stacks[$parentId])) {
                    $stacks[$parentId] = [];
                }
                $stacks[$parentId][] = $row;
                unset($rows[$index]);
            } else {
                $stacks[$row['hierarchy_id']] = [$row];
            }
        }

        return $stacks;
    }
}
