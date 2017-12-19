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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Renderer;

use DeskPRO\Bundle\AppBundle\Dpql2\Renderer\Values\Html as HtmlValues;
use DeskPRO\Bundle\AppBundle\Dpql2\Renderer\Values\Text;
use DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler;

/**
 * Renders DPQL results to HTML.
 */
class Html extends AbstractRenderer
{
    /**
     * Internal handler used when rendering to count how many rows
     * row spans need to be used for.
     *
     * @var array
     */
    protected $_rowSpans = [];

    /**
     * Internal handler used when rendering to determine which row groups
     * have been "hit" and printed.
     *
     * @var array
     */
    protected $_rowGroupHit = [];

    /**
     * Get the default value renderer that should be used for this type.
     *
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Renderer\Values\AbstractValues
     */
    protected function _getDefaultValueRenderer()
    {
        return new HtmlValues();
    }

    /**
     * Gets the MIME content type for this type of output.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'text/html';
    }

    /**
     * Gets the file extension for this type of output.
     *
     * @return string
     */
    public function getExtension()
    {
        return 'html';
    }

    /**
     * Joins the already rendered output into one output.
     *
     * @param array $output
     *
     * @return string
     */
    protected function _implodeSplitOutput(array $output)
    {
        return implode("\n\n", $output);
    }

    /**
     * Finalizes the rendering of a split output by rendering the body with the header.
     *
     * @param string $header
     * @param string $body
     *
     * @return string
     */
    public function _renderSplitOutputWithHeader($header, $body)
    {
        return '<h3 class="report-split-header">'.$header.'</h3>'."\n$body";
    }

    /**
     * Renders a table with the specified rows/data.
     *
     * @param array $rows
     *
     * @return string
     */
    protected function _renderTable(array $rows)
    {
        if (!$rows) {
            return '';
        }

        // If data is hierarchical, then X-grouping is applied to bar charts only, in tables hierarchy
        // is denoted by sorting and indentation
        if ($this->_handler->getGroupXColumns() && !$this->_handler->hasFlag(ResultHandler::FLAG_HIERARCHICAL)) {
            return $this->_renderMatrixTable($rows);
        }

        return $this->_renderTableWrapper(
            $this->_renderHeader()
            .$this->_renderBody($rows)
            .$this->_renderFooter($rows)
        );
    }

    /**
     * Renders the outer table wrapper.
     *
     * @param string $inner      Content inside table
     * @param string $extraClass Any extra classes to add (space separated)
     *
     * @return string
     */
    protected function _renderTableWrapper($inner, $extraClass = '')
    {
        return "<table class=\"report-builder-table $extraClass\" cellspacing=\"0\">\n$inner\n</table>\n";
    }

    /**
     * Renders the header row (for a simple table).
     *
     * @return string
     */
    protected function _renderHeader()
    {
        $columnHtml = [];
        foreach ($this->_handler->getGroupYColumns() as $column) {
            $columnHtml[] = '<th>'.$this->_valueRenderer->escapeValue($column['title']).'</th>';
        }
        foreach ($this->_handler->getSelectColumns() as $column) {
            $columnHtml[] = '<th>'.$this->_valueRenderer->escapeValue($column['title']).'</th>';
        }

        if ($this->_withRollup()) {
            $columnHtml[] = '<th>Total</th>';
        }

        return '<thead><tr class="row-header">'.implode("\n\t", $columnHtml).'</tr></thead>';
    }

    /**
     * Renders the body of a "simple" table.
     *
     * @param array $rows
     *
     * @return string
     */
    protected function _renderBody(array $rows)
    {
        $groupColumns  = $this->_handler->getGroupYColumns();
        $selectColumns = $this->_handler->getSelectColumns();
        $rows          = array_values($rows); // need continuous keys

        $rowsHtml = [];
        $rowCount = 0;

        $groupSkipCount = [];
        foreach ($groupColumns as $groupId => $groupColumn) {
            $groupSkipCount[$groupId] = 0;
        }

        foreach ($rows as $rowId => $row) {
            $cells = [];

            if ($groupColumns) {
                $myGroupSkipCount = $groupSkipCount;

                $groupValues = [];
                foreach ($groupColumns as $groupId => $groupColumn) {
                    $groupValues[$groupId] = $this->getColumnValue($row, $groupColumn['groupResultId']);
                }

                $nextRowId = $rowId + 1;
                if (isset($rows[$nextRowId])) {
                    $firstNonMatch = null;
                    for (; isset($rows[$nextRowId]); ++$nextRowId) {
                        $nextRow = $rows[$nextRowId];
                        $matched = 0;

                        foreach ($groupColumns as $groupId => $groupColumn) {
                            if ($firstNonMatch !== null && $firstNonMatch == $groupId) {
                                // can't go any further as this column doesn't match from before
                                break;
                            }

                            $groupValue = $this->getColumnValue($nextRow, $groupColumn['groupResultId']);
                            if ($groupValues[$groupId] == $groupValue) {
                                ++$matched;
                                if (!$myGroupSkipCount[$groupId]) {
                                    // if there's a skip count for this, we don't need to increase it
                                    // as it's already been accounted for
                                    ++$groupSkipCount[$groupId];
                                }
                            } else {
                                $firstNonMatch = $groupId;
                                break;
                            }
                        }

                        if (!$matched) {
                            break;
                        }
                    }
                }

                foreach ($groupColumns as $groupId => $groupColumn) {
                    if ($myGroupSkipCount[$groupId]) {
                        --$groupSkipCount[$groupId];
                        continue;
                    }

                    $rowSpan = ($groupSkipCount[$groupId]
                        ? ' rowspan="'.($groupSkipCount[$groupId] + 1).'"'
                        : ''
                    );

                    $padding = ($this->_handler->hasFlag(ResultHandler::FLAG_HIERARCHICAL) && empty($cells))
                              ? $this->_getRowPadding($row)
                              : '';
                    $rendered = $padding.$this->_renderCellValue($row, $groupColumn);
                    $cells[]  = "<th$rowSpan>{$rendered}</th>";
                }
            }

            foreach ($selectColumns as $column) {
                $padding = ($this->_handler->hasFlag(ResultHandler::FLAG_HIERARCHICAL) && empty($cells))
                          ? $this->_getRowPadding($row)
                          : '';
                $rendered = $padding.$this->_renderCellValue($row, $column);
                $cells[]  = '<td>'.$rendered.'</td>';
            }
            if ($this->_withRollup()) {
                $cells[] = '<td>'.$this->_renderCellValue($row, 'hierarchy_rollup_count').'</td>';
            }

            ++$rowCount;
            $class = ($rowCount % 2 ? 'odd' : 'even');

            $rowsHtml[] = '<tr class="row-body '.$class.'">'.implode("\n\t", $cells).'</tr>';
        }

        if ($rowsHtml) {
            return '<tbody>'.implode("\n", $rowsHtml).'</tbody>';
        } else {
            return '';
        }
    }

    private function _getRowPadding(array $row)
    {
        $padding = '';
        if (array_key_exists('hierarchy_depth', $row) && ($depth = $row['hierarchy_depth'])) {
            $padding = str_repeat('&nbsp;', 4 * $depth).'&#8209;&#8209;&nbsp;';
        }

        return $padding;
    }

    protected function _renderFooter(array $rows)
    {
        $totalColumns = $this->_handler->getTotalColumns();
        if (count($rows) < 2 || !$totalColumns) {
            return '';
        }

        $cells         = [];
        $groupYColumns = $this->_handler->getGroupYColumns();
        $selectColumns = $this->_handler->getSelectColumns();

        if ($groupYColumns) {
            $cells[] = '<th colspan="'.count($groupYColumns).'">Total</th>';
        }

        $columnTotals = [];
        foreach ($rows as $row) {
            foreach ($totalColumns as $id) {
                if ($this->getColumnValue($row, $id) === null) {
                    continue;
                }

                if (!isset($columnTotals[$id])) {
                    $columnTotals[$id] = 0;
                }
                $columnTotals[$id] += (int) $this->getColumnValue($row, $id);
            }
        }

        $firstRow = reset($rows);
        $fakeRow  = array_fill_keys(array_keys($firstRow), null);
        foreach ($columnTotals as $id => $value) {
            $fakeRow[$id - 1] = $value;
        }

        foreach ($selectColumns as $column) {
            if (isset($columnTotals[$column['resultId']])) {
                $cells[] = '<td>'.$this->_renderCellValue($fakeRow, $column).'</td>';
            } else {
                $cells[] = '<td>&nbsp;</td>';
            }
        }

        return '<tfoot><tr class="row-body total-row">'.implode('', $cells).'</tr></tfoot>';
    }

    /**
     * Renders a matrix table (with X and Y grouping).
     *
     * @param array $rows
     *
     * @return string
     */
    protected function _renderMatrixTable(array $rows)
    {
        $prepared = $this->_prepareMatrixTable($rows);

        $select = $this->_handler->getSelectColumns();
        $first  = reset($select);
        if (count($select) == 1 && in_array($first['renderer'], ['number', 'numberraw'], true)) {
            $totalType = $first['renderer'];
        } else {
            $totalType = false;
        }

        return $this->_renderTableWrapper(
            $this->_renderMatrixHeader($prepared, $totalType).$this->_renderMatrixBody($prepared, $totalType),
            'matrix'
        );
    }

    /**
     * Renders the header rows of a matrix table.
     *
     * @param array       $prepared  Prepared matrix data (see _prepareMatrixTable)
     * @param bool|string $totalType If non empty, shows a total for each row/column
     *
     * @return string
     */
    protected function _renderMatrixHeader(array $prepared, $totalType = false)
    {
        $rowSkipCount = count($this->_handler->getGroupXColumns());
        $colSkipCount = count($this->_handler->getGroupYColumns());

        $header = $this->_renderMatrixHeaderRecur(['root'], $prepared['xDistinct']);
        $rows   = $header['depth'];
        ksort($rows);

        $output = [];

        $row = [];
        if ($colSkipCount) {
            $row[] = '<th'.($colSkipCount > 1 ? " colspan=\"$colSkipCount\"" : '').'>&nbsp;</th>';
        }
        $parts = [];
        foreach ($this->_handler->getGroupXColumns() as $column) {
            $parts[] = $column['title'];
        }
        $row[] = '<th colspan="'.$header['colSpan'].'" class="label">'
            .$this->_valueRenderer->escapeValue(implode(' / ', $parts))
            .'</th>';
        if ($totalType) {
            $row[] = '<th>&nbsp;</th>';
        }
        $output[] = '<tr class="row-header">'.implode('', $row).'</tr>';

        foreach ($rows as $depth => $row) {
            if ($depth === 0) {
                $rowSpan = ($rowSkipCount > 1 ? " rowspan=\"$rowSkipCount\"" : '');

                if ($colSkipCount) {
                    $prefix = '';
                    foreach ($this->_handler->getGroupYColumns() as $column) {
                        $prefix .= '<th'.$rowSpan.' class="label">'.$this->_valueRenderer->escapeValue($column['title']).'</th>';
                    }
                    $row = $prefix.$row;
                }
                if ($totalType) {
                    $row .= "<th class=\"column-total\" $rowSpan>Total</th>";
                }
            }
            $output[] = "<tr class=\"row-header\">$row</tr>";
        }

        if ($output) {
            return '<thead>'.implode("\n\t", $output).'</thead>';
        } else {
            return '';
        }
    }

    /**
     * Internal helper to render matrix table header rows.
     *
     * Returns array with keys:
     *  - colSpan -- number of columns spanned by children
     *  - depth -- array of HTML for each depth below this one
     *
     * @param array $path           Grouping path
     * @param array $distinctValues
     * @param int   $depth
     *
     * @return array
     */
    protected function _renderMatrixHeaderRecur(array $path, array $distinctValues, $depth = 0)
    {
        $pathLookup = $this->_getGroupPathKey($path);
        if (!isset($distinctValues[$pathLookup])) {
            return ['colSpan' => 0, 'depth' => []];
        }

        $colSpan  = 0;
        $siblings = [];

        $nextDepth = $depth + 1;
        $depthHtml = [];

        foreach ($distinctValues[$pathLookup] as $groupValue => $printValue) {
            $localPath   = $path;
            $localPath[] = $groupValue;

            $child = $this->_renderMatrixHeaderRecur($localPath, $distinctValues, $nextDepth);

            foreach ($child['depth'] as $level => $childDepthHtml) {
                if (!isset($depthHtml[$level])) {
                    $depthHtml[$level] = '';
                }
                $depthHtml[$level] .= $childDepthHtml;
            }

            $colSpan += max(1, $child['colSpan']);

            $colSpanHtml = ($child['colSpan'] > 1 ? ' colspan="'.$child['colSpan'].'"' : '');
            $valueHtml   = "<th$colSpanHtml>$printValue</th>";

            $siblings[] = $valueHtml;
        }

        $depthHtml[$depth] = implode('', $siblings);

        return [
            'colSpan' => $colSpan,
            'depth'   => $depthHtml,
        ];
    }

    /**
     * Renders the body of a matrix table.
     *
     * @param array       $prepared  Prepared matrix data
     * @param bool|string $totalType If non empty, shows a total for each row/column
     *
     * @return string
     */
    protected function _renderMatrixBody(array $prepared, $totalType = false)
    {
        if (!$prepared['yDistinct']) {
            // no Y grouping - that means we can have one row so fake it
            $rowKeys = ['root' => ''];
        } else {
            $rowKeys = $this->_getMatrixRowGroups(['root'], $prepared['yDistinct']);
        }

        $matrixPaths = $this->_getFinalMatrixPaths(['root'], $prepared['xDistinct']);
        $lookup      = $prepared['lookup'];

        $rows         = [];
        $columnTotals = [];
        $rowCount     = 0;

        foreach ($rowKeys as $yPath => $html) {
            $cells    = [];
            $rowTotal = 0;
            foreach ($matrixPaths as $xPath) {
                if (isset($lookup[$yPath][$xPath])) {
                    $value = $lookup[$yPath][$xPath];
                } else {
                    $value = '';
                }
                $cells[] = "<td>$value</td>";

                if ($totalType) {
                    $rowTotal += (int) str_replace(',', '', $value);
                    if (!isset($columnTotals[$xPath])) {
                        $columnTotals[$xPath] = 0;
                    }
                    $columnTotals[$xPath] += (int) str_replace(',', '', $value);
                }
            }

            if ($totalType) {
                $cells[] = '<td class="column-total">'.$this->_valueRenderer->renderValue($rowTotal, $totalType).'</td>';
            }

            ++$rowCount;
            $class = ($rowCount % 2 ? 'odd' : 'even');

            $rows[] = '<tr class="row-body '.$class.'">'.$html.implode('', $cells).'</tr>';
        }

        if ($totalType && $this->_handler->getGroupYColumns()) {
            $cells   = [];
            $cells[] = '<th colspan="'.count($this->_handler->getGroupYColumns()).'">Total</th>';
            foreach ($columnTotals as $value) {
                $cells[] = '<td>'.$this->_valueRenderer->renderValue($value, $totalType).'</td>';
            }
            $cells[] = '<td class="column-total">'.$this->_valueRenderer->renderValue(array_sum($columnTotals), $totalType).'</td>';

            ++$rowCount;
            $class = ($rowCount % 2 ? 'odd' : 'even');

            $rows[] = '<tr class="row-body '.$class.' total-row">'.implode('', $cells).'</tr>';
        }

        if ($rows) {
            return '<tbody>'.implode("\n\t", $rows).'</tbody>';
        } else {
            return '';
        }
    }

    /**
     * Gets the groupings that will represent rows in a matrix tables, including
     * ultimate Y paths.
     *
     * @param array $path      Grouping path to this point
     * @param array $yDistinct Distinct values in the Y direction
     *
     * @return array HTML for each unique row of Y grouping columns
     */
    protected function _getMatrixRowGroups(array $path, array $yDistinct)
    {
        $pathString = $this->_getGroupPathKey($path);
        if (!isset($yDistinct[$pathString])) {
            return [];
        }

        $output = [];
        foreach ($yDistinct[$pathString] as $groupValue => $printValue) {
            $localPath   = $path;
            $localPath[] = $groupValue;

            $children = $this->_getMatrixRowGroups($localPath, $yDistinct);
            if (!$children) {
                $output[$this->_getGroupPathKey($localPath)] = '<th>'.$printValue.'</th>';
            } else {
                $rowSpan     = count($children);
                $rowSpanHtml = ($rowSpan > 1 ? " rowspan=\"$rowSpan\"" : '');

                $first = '<th'.$rowSpanHtml.'>'.$printValue.'</th>';

                foreach ($children as $key => $child) {
                    $output[$key] = $first.$child;
                    $first        = '';
                }
            }
        }

        return $output;
    }

    /**
     * Renders a chart with the specified rows/data.
     *
     * @param string $type Type of chart (bar, line, pie)
     * @param array  $rows
     *
     * @return string|bool
     */
    protected function _renderChart($type, array $rows)
    {
        $optionMap = [
            'bar' => '
                graph.type = "column";
                graph.fillAlphas = 1;
            ',
            'line' => '
                graph.type = "line";
                graph.lineThickness = 2;
                graph.bullet = "round";
                graph.bulletSize = 6;
            ',
            'area' => '
                graph.type = "line";
                graph.lineThickness = 1;
                graph.bullet = "round";
                graph.bulletSize = 4;
                graph.fillAlphas = 0.6;
            ',
            'pie' => '',
        ];
        if (!isset($optionMap[$type])) {
            return false;
        }

        if (!$rows) {
            return '';
        }

        $originalValueRenderer = $this->_valueRenderer;
        $this->_valueRenderer  = new Text();

        $selectColumns = $this->_handler->getSelectColumns();
        $groupYColumns = $this->_handler->getGroupYColumns();
        $groupXColumns = $this->_handler->getGroupXColumns();

        $chartData         = [];
        $graphs            = [];
        $isStacked         = false;
        $maxCategoryLength = 0;

        $firstSel       = reset($selectColumns);
        $valueAxisTitle = $firstSel['title'];

        if ($groupXColumns) {
            // matrix table - X() values translate to bottom axis, each row (from Y()) is a new line/stack.
            $prepared = $this->_prepareMatrixTable($rows);
            $lookup   = $prepared['lookup'];

            $rowGroups = $this->_getFinalMatrixPathsWithPrintable(['root'], $prepared['yDistinct']);
            if (!$rowGroups) {
                // need to fake it so we get a row with no Y grouping
                $rowGroups = ['root' => []];
            }
            $headerCols = $this->_getFinalMatrixPathsWithPrintable(['root'], $prepared['xDistinct']);

            foreach ($headerCols as $xPath => $printable) {
                $category = $this->_handler->hasFlag(ResultHandler::FLAG_HIERARCHICAL)
                                   ? str_replace('root|', '', $xPath)
                                   : implode(' / ', $printable);
                $maxCategoryLength = max($maxCategoryLength, strlen($category));

                $rowData = ['category' => $category];

                $i = 0;
                foreach ($rowGroups as $yPath => $null) {
                    if (isset($lookup[$yPath][$xPath])) {
                        $value = $this->_filterGraphValue($lookup[$yPath][$xPath]);
                    } else {
                        $value = '';
                    }
                    $rowData['value'.$i] = $value;
                    ++$i;
                }

                $chartData[] = $rowData;
            }

            $i = 0;
            foreach ($rowGroups as $printable) {
                $graphs[$i] = [
                    'title' => implode(' / ', $printable),
                    'value' => "value$i",
                ];
                ++$i;
            }

            $hasCategory = true;
            $isStacked   = ($type == 'bar' || $type == 'area');

            $parts = [];
            foreach ($groupXColumns as $column) {
                $parts[] = $column['title'];
            }
            $categoryAxisTitle = implode(' / ', $parts);
        } else {
            if (count($groupYColumns) > 1) {
                $rowGroups = [];
                foreach ($rows as $row) {
                    $categories = [];
                    $grouper    = '';
                    $i          = 0;
                    foreach ($groupYColumns as $column) {
                        ++$i;
                        if ($i == 1) {
                            $grouper = $this->_renderCellValue($row, $column);
                            continue;
                        } else {
                            $categories[] = $this->_renderCellValue($row, $column);
                        }
                    }
                    $category = implode(' / ', $categories);

                    $rowData = [];

                    foreach ($selectColumns as $i => $column) {
                        $rowData['value'.$i] = $this->_filterGraphValue($this->getColumnValue($row, $column));
                    }

                    $rowGroups[$grouper][$category] = $rowData;
                }

                $uniqueGraphs = [];

                foreach ($rowGroups as $grouper => $values) {
                    $maxCategoryLength = max($maxCategoryLength, strlen($grouper));

                    $data = ['category' => $grouper];
                    foreach ($values as $categoryName => $groupValues) {
                        $uniqueGraphs[$categoryName] = true;
                        foreach ($groupValues as $valueId => $value) {
                            $data["$categoryName-$valueId"] = $value;
                        }
                    }

                    $chartData[] = $data;
                }

                foreach ($uniqueGraphs as $categoryName => $null) {
                    $graphs[] = [
                        'title' => "$categoryName",
                        'value' => "$categoryName-value0",
                    ];
                }

                $isStacked = ($type == 'bar' || $type == 'area');

                $firstY            = reset($groupYColumns);
                $categoryAxisTitle = $firstY['title'];
            } elseif ($this->_handler->getGroupStackColumns() && $type == 'bar') {
                $stackColumns = $this->_handler->getGroupStackColumns();

                $rowGroups = [];
                foreach ($rows as $row) {
                    $categories = [];
                    $grouper    = $this->_valueRenderer->renderValue($this->getColumnValue($row, $stackColumns[0]['printId']), 'string');
                    foreach ($groupYColumns as $column) {
                        $categories[] = $this->_renderCellValue($row, $column);
                    }
                    $category = implode(' / ', $categories);

                    $rowData = [];

                    foreach ($selectColumns as $i => $column) {
                        $rowData['value'.$i] = $this->_filterGraphValue($this->getColumnValue($row, $column));
                    }

                    $rowGroups[$grouper][$category] = $rowData;
                }

                $uniqueGraphs = [];

                foreach ($rowGroups as $grouper => $values) {
                    $maxCategoryLength = max($maxCategoryLength, strlen($grouper));

                    $data = ['category' => $grouper];
                    foreach ($values as $categoryName => $groupValues) {
                        $uniqueGraphs[$categoryName] = true;
                        foreach ($groupValues as $valueId => $value) {
                            $data["$categoryName-$valueId"] = $value;
                        }
                    }

                    $chartData[] = $data;
                }

                foreach ($uniqueGraphs as $categoryName => $null) {
                    $graphs[] = [
                        'title' => "$categoryName",
                        'value' => "$categoryName-value0",
                    ];
                }

                $isStacked = ($type == 'bar' || $type == 'area');

                $firstY            = reset($groupYColumns);
                $categoryAxisTitle = $firstY['title'];
            } else {
                $sel = reset($selectColumns);

                foreach ($rows as $row) {
                    $categories = [];
                    foreach ($groupYColumns as $column) {
                        $categories[] = $this->_renderCellValue($row, $column);
                    }
                    $category = implode(' / ', $categories);

                    $maxCategoryLength = max($maxCategoryLength, strlen($category));

                    $rowData = ['category' => $category];

                    $rowData['value'] = $this->_filterGraphValue($this->getColumnValue($row, $sel));

                    $chartData[] = $rowData;
                }

                $graphs[] = [
                    'title' => $sel['title'],
                    'value' => 'value',
                ];

                $parts = [];
                foreach ($groupYColumns as $column) {
                    $parts[] = $column['title'];
                }
                $categoryAxisTitle = implode(' / ', $parts);
            }

            $hasCategory = count($groupYColumns) > 0;
        }

        if ($type == 'pie') {
            $output = '';

            $sliceCount = count($chartData);
            if ($maxCategoryLength > 25) {
                $divisor = 1;
            } elseif ($maxCategoryLength > 15) {
                $divisor = 2;
            } else {
                $divisor = 4;
            }
            $height = 400 + ceil($sliceCount / $divisor) * 30;

            $pieData = [];

            if (count($graphs) > 1) {
                foreach ($chartData as $key => $info) {
                    $data = [];
                    foreach ($graphs as $graph) {
                        if (isset($info[$graph['value']])) {
                            $data[] = [
                                'category' => $graph['title'],
                                'value'    => $info[$graph['value']],
                            ];
                        }
                    }

                    $pieData[] = [
                        'title' => $info['category'],
                        'data'  => $data,
                    ];
                }

                // let's add a graph for the first level of grouping
                $data = [];
                foreach ($pieData as $pie) {
                    $sum = 0;
                    foreach ($pie['data'] as $info) {
                        $sum += (int) $info['value'];
                    }
                    $data[] = [
                        'category' => $pie['title'],
                        'value'    => $sum,
                    ];
                }

                array_unshift($pieData, [
                    'title' => 'Overall',
                    'data'  => $data,
                ]);
            } else {
                $graph = reset($graphs);

                $pieData = [[
                    'title' => $graph['title'],
                    'data'  => $chartData,
                ]];
            }

            $showTitle = count($pieData) > 1;

            foreach ($pieData as $pie) {
                $id = 'report_chart_'.md5(uniqid());

                $output .= '
                    <div id="'.$id.'" class="report-chart" style="height: '.$height.'px"></div>
                    <script type="text/javascript">
                    $(function () {
                        var chart = new AmCharts.AmPieChart();
                        chart.dataProvider = '.json_encode($pie['data']).';
                        chart.titleField = "category";
                        chart.valueField = "value";
                        chart.startDuration = 0;
                        '.(count($pie['data']) >= 25 ? 'chart.labelsEnabled = false;' : '').'
                        chart.addLegend(new AmCharts.AmLegend());
                        '.($showTitle ? 'chart.addTitle('.json_encode($pie['title']).');' : '').'

                        chart.write("'.$id.'");
                    });
                    </script>
                ';
            }
        } else {
            if ($hasCategory) {
                $balloonText = '[[category]], [[title]]: [[value]]';
            } else {
                $balloonText = '[[title]]: [[value]]';
            }

            $graphCode = [];
            foreach ($graphs as $graph) {
                $graphCode[] = '
                    graph = new AmCharts.AmGraph();
                    graph.valueField = "'.$graph['value'].'";
                    graph.title = "'.$this->_jsEscapeValue($graph['title']).'";
                    graph.balloonText = "'.$balloonText.'";
                    '.$optionMap[$type].'
                    chart.addGraph(graph);
                ';
            }

            if ($isStacked) {
                $stacked = 'chart.valueAxes[0].stackType = "regular";';
            } else {
                $stacked = '';
            }

            $height = 430 + count($graphs) * 25;

            if ($maxCategoryLength > 10) {
                $labelHeight    = $maxCategoryLength * 4;
                $verticalLabels = '
                    chart.categoryAxis.labelRotation = 45;
                    chart.categoryAxis.gridCount = '.min(15, count($rows)).';
                    chart.marginBottom = '.$labelHeight.';
                ';
                $height += (int) $labelHeight;
            } else {
                $verticalLabels = '';
            }

            if ($isStacked) {
                $chartData = $this->_fillInGraphValues($chartData);
            }

            $percent_code = '';
            if (isset($selectColumns[0]) && isset($selectColumns[0]['renderer']) && $selectColumns[0]['renderer'] == 'percent') {
                $percent_code = '
                    valueAxis.maximum = 100;
                    valueAxis.minimum = 0;
                ';
            }

            $id     = 'report_chart_'.md5(uniqid());
            $output = '
                <div id="'.$id.'" class="report-chart" style="height: '.$height.'px"></div>
                <script type="text/javascript">
                $(function () {
                    var chart = new AmCharts.AmSerialChart();
                    chart.dataProvider = '.json_encode($chartData).';
                    chart.categoryField = "category";
                    chart.addLegend(new AmCharts.AmLegend());

                    chart.categoryAxis.fontSize = 9;
                    chart.categoryAxis.title = \''.$this->_jsEscapeValue($categoryAxisTitle).'\';
                    '.$verticalLabels.'

                    var valueAxis = new AmCharts.ValueAxis();
                    '.$percent_code.'

                    chart.addValueAxis(valueAxis);
                    chart.valueAxes[0].integersOnly = true;
                    chart.valueAxes[0].title = \''.$this->_jsEscapeValue($valueAxisTitle).'\';
                    '.$stacked.'

                    var graph;
                    '.implode("\n", $graphCode).'

                    chart.write("'.$id.'");
                    chart.invalidateSize();
                });
                </script>
            ';
        }

        $this->_valueRenderer = $originalValueRenderer;

        return $output;
    }

    protected function _fillInGraphValues($chartData)
    {
        $uniqueValues = [];
        foreach ($chartData as $values) {
            foreach ($values as $value => $null) {
                if (!isset($uniqueValues[$value])) {
                    $uniqueValues[$value] = true;
                }
            }
        }
        foreach ($chartData as &$values) {
            foreach ($uniqueValues as $value => $null) {
                if (!isset($values[$value])) {
                    $values[$value] = 0;
                }
            }
        }

        return $chartData;
    }

    protected function _jsEscapeValue($value)
    {
        return strtr($value, [
            '"'         => '\\"',
            "'"         => "\\'",
            '\\'        => '\\\\',
            '</script>' => '<\\/script>',
        ]);
    }

    /**
     * Filters a graph value that looks like a number into an actual number.
     *
     * @param string $value
     *
     * @return string|number
     */
    protected function _filterGraphValue($value)
    {
        if (preg_match('/^((\d+,)*\d+)(\.\d+)?%?$/', $value)) {
            return round(str_replace([',', '%'], '', $value) + 0, 1);
        } else {
            return $value;
        }
    }

    /**
     * @return bool
     */
    protected function _withRollup()
    {
        return $this->_handler->hasFlag(ResultHandler::FLAG_HIERARCHICAL)
               && $this->_handler->hasFlag(ResultHandler::FLAG_WITH_ROLLUP);
    }
}
