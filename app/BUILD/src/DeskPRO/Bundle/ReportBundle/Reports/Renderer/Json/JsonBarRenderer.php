<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
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
        // stack results if simple grouping only, e.g. can't stack for matrix
        if (count($groupXColumns) === 1
            && count($groupYColumns) === 1
            && $metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)
            && !$metadata->hasFlag(ResultMetadata::FLAG_LAYERED)
        ) {
            $arrayOutput['valueAxes'][0]['stackType'] = 'regular';
            $arrayOutput['valueAxes'][0]['title']     = $selectColumns[0]['title'];
            $arrayOutput['categoryAxis']['title']     = $groupYColumns[0]['title'];

            $hierarchyParents = $this->collectHierarchyParents($rows);
            $stacks           = $this->getStacks($rows, $hierarchyParents);

            foreach ($stacks as $i => $stack) {
                $chartData[$i] = [];
                foreach ($stack as $j => $values) {
                    $chartData[$i]['category']  = isset($hierarchyParents[$i]) ? $hierarchyParents[$i]['hierarchy_root_title'] : $values['hierarchy_root_title'];
                    $chartData[$i]["value$i$j"] = $values[$selectColumns[0]['resultId'] - 1];
                    $graphs[]                   = [
                        'id'          => "graph-$i-$j",
                        'type'        => 'column',
                        'fillAlphas'  => '0.9',
                        'valueField'  => "value$i$j",
                        'title'       => $this->getFullHierarchyTitle($values, $hierarchyParents),
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
     * @param array $hierarchyParents
     *
     * @return array
     */
    protected function getStacks(array &$rows, array $hierarchyParents)
    {
        $stacks   = [];
        $iterator = function ($row, $parentId) use ($hierarchyParents, &$stacks, &$iterator) {
            $id       = (int) $row['hierarchy_id'];
            $parentId = (int) $parentId;

            if (array_key_exists($parentId, $hierarchyParents)) {
                $hierarchyParent = $hierarchyParents[$parentId];
                if (isset($hierarchyParent['hierarchy_parent_id']) && $hierarchyParent['hierarchy_parent_id']) {
                    $iterator($row, $hierarchyParent['hierarchy_parent_id']);
                } else {
                    $stacks[$parentId][] = $row;
                }
            } else {
                $stacks[$id] = [$row];
            }
        };

        foreach ($rows as $index => $row) {
            $iterator($row, $row['hierarchy_parent_id']);
        }

        return $stacks;
    }
}
