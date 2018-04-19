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

            $hierarchyParents = $this->collectHierarchyParents($rows);
            $stacks           = $this->getStacks($rows, $hierarchyParents);

            $additionalData = [];
            foreach ($selectColumns as $selectColumn) {
                if (strpos($selectColumn['title'], '__var') !== false) {
                    $additionalData[$selectColumn['title']] = $selectColumn['resultId'];
                }
            }

            foreach ($stacks as $i => $stack) {
                $chartData[$i] = [];
                foreach ($stack as $j => $values) {
                    $chartData[$i]['category']  = isset($hierarchyParents[$i]) ? $hierarchyParents[$i]['hierarchy_root_title'] : $values['hierarchy_root_title'];
                    $chartData[$i]["value$i$j"] = $this->renderCellValue($values, $selectColumns[0], $metadata, true);
                    $values[$selectColumns[0]['resultId'] - 1];

                    foreach ($additionalData as $key => $resultId) {
                        $chartData[$i][$key] = $this->renderCellValue($values, $selectColumns[0], $metadata, true);
                        $values[$resultId - 1];
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

            $hash = $this->getCollectedHash();
            if ($hash) {
                $hash = array_flip(array_keys($hash));
                array_walk($hash, function (&$item) {
                    ++$item;
                });
                foreach ($chartData as &$chartDatum) {
                    foreach ($chartDatum as $key => &$value) {
                        if (strpos($key, 'value') !== false) {
                            $value = $hash[$value];
                        }
                    }
                }
                $arrayOutput['valueAxes'][0]['hash'] = array_flip($hash);
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
