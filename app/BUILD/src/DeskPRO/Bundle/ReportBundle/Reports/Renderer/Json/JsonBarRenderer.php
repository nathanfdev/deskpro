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

        $valueLabelTemplate = $categoryLabelTemplate = null;
        $integersOnly       = true;

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
            foreach ($selectColumns as $index => $selectColumn) {
                if (strpos($selectColumn['title'], '__var') !== false) {
                    $additionalData[$selectColumn['title']] = $selectColumn['resultId'];
                }
                // it would be same for each row of course since it
                // SELECT blah-blah-blah
                // '{{value * 4}} as 'value_label_template'
                if ($selectColumn['title'] === 'value_label_template') {
                    $valueLabelTemplate = $rows[0][$selectColumn['resultId'] - 1];
                    unset($selectColumns[$index]);
                }
                if ($selectColumn['title'] === 'category_label_template') {
                    $categoryLabelTemplate = $rows[0][$selectColumn['resultId'] - 1];
                    unset($selectColumns[$index]);
                }
            }

            foreach ($stacks as $i => $stack) {
                $chartData[$i] = [];
                foreach ($stack as $j => $values) {
                    $category                  = isset($hierarchyParents[$i]) ? $hierarchyParents[$i]['hierarchy_root_title'] : $values['hierarchy_root_title'];
                    $maxCategoryLength         = max($maxCategoryLength, strlen($category));
                    $chartData[$i]['category'] = $category ?: 'None';

                    $value = $values[$selectColumns[0]['resultId'] - 1];

                    /*
                     * @see https://deskpro.myjetbrains.com/youtrack/issue/DP-1503
                     */
                    if (!$integersOnly || !filter_var($value, FILTER_VALIDATE_INT)) {
                        $integersOnly = false;
                    }

                    $chartData[$i]["value$i$j"] = $value;

                    foreach ($additionalData as $key => $resultId) {
                        $value = $values[$resultId - 1];
                        if (!filter_var($value, FILTER_VALIDATE_INT)) {
                            $integersOnly = false;
                        }
                        $chartData[$i][$key] = $value;
                    }

                    $title = $this->renderCellValue($values, $selectColumns[0], $metadata);
                    if ($value == $title) {
                        $balloonText = '[[category]]: [[value]]';
                        $title       = $this->getFullHierarchyTitle($values, $hierarchyParents).': '.$value;
                    } else {
                        $title       = $this->getFullHierarchyTitle($values, $hierarchyParents).': '.$title;
                        $balloonText = '[[title]]';
                    }
                    $graphs[] = [
                        'id'          => "graph-$i-$j",
                        'type'        => 'column',
                        'fillAlphas'  => '0.9',
                        'valueField'  => "value$i$j",
                        'title'       => $title,
                        'balloonText' => $balloonText,
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
            $arrayOutput['valueAxes'][0]['integersOnly']  = $integersOnly;
            $arrayOutput['valueAxes'][0]['labelTemplate'] = $valueLabelTemplate;
            $arrayOutput['categoryAxis']['labelTemplate'] = $categoryLabelTemplate;

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
