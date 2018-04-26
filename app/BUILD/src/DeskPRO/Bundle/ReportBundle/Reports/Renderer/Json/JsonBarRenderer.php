<?php

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

            $hierarchyParents  = $this->collectHierarchyParents($rows);
            $stacks            = $this->getStacks($rows, $hierarchyParents);
            $maxCategoryLength = 0;

            $additionalData = [];
            foreach ($selectColumns as $selectColumn) {
                if (strpos($selectColumn['title'], '__var') !== false) {
                    $additionalData[$selectColumn['title']] = $selectColumn['resultId'];
                }
            }

            foreach ($stacks as $i => $stack) {
                $chartData[$i] = [];
                foreach ($stack as $j => $values) {
                    $category                   = isset($hierarchyParents[$i]) ? $hierarchyParents[$i]['hierarchy_root_title'] : $values['hierarchy_root_title'];
                    $maxCategoryLength          = max($maxCategoryLength, strlen($category));
                    $chartData[$i]['category']  = $category;
                    $chartData[$i]["value$i$j"] = $values[$selectColumns[0]['resultId'] - 1];

                    foreach ($additionalData as $key => $resultId) {
                        $chartData[$i][$key] = $values[$resultId - 1];
                    }

                    $graphs[] = [
                        'id'          => "graph-$i-$j",
                        'type'        => 'column',
                        'fillAlphas'  => '0.9',
                        'valueField'  => "value$i$j",
                        'title'       => $this->getFullHierarchyTitle($values, $hierarchyParents).', '.$this->renderCellValue($values, $selectColumns[0], $metadata),
                        'balloonText' => '[[title]]:[[value]]',
                    ];
                }
            }

            $arrayOutput['dataProvider'] = array_values($chartData);
            $arrayOutput['graphs']       = array_values($graphs);
            if ($maxCategoryLength > 10) {
                $labelHeight                 = $maxCategoryLength * 4;
                $arrayOutput['categoryAxis'] = array_merge($arrayOutput['categoryAxis'], [
                    'labelRotation' => 45,
                    'gridCount'     => min(15, count($rows)),
                    'marginBottom'  => $labelHeight,
                ]);
            }

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
