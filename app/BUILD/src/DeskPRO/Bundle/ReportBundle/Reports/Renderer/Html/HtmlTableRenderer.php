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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class HtmlTableRenderer.
 */
class HtmlTableRenderer extends AbstractHtmlRenderer
{
    /**
     * Constructor.
     *
     * @param HtmlValueRenderer $valueRenderer
     */
    public function __construct(HtmlValueRenderer $valueRenderer)
    {
        $this->valueRenderer = $valueRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_TABLE;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return '';
        }

        // If data is hierarchical, then X-grouping is applied to bar charts only, in tables hierarchy
        // is denoted by sorting and indentation
        if ($metadata->getGroupXColumns() && !$metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)) {
            return $this->renderMatrixTable($metadata, $rows);
        }

        return $this->renderTableWrapper(
            $this->renderHeader($metadata)
            .$this->renderBody($metadata, $rows)
            .$this->renderFooter($metadata, $rows)
        );
    }

    /**
     * Renders a matrix table (with X and Y grouping).
     *
     * @param ResultMetadata $resultHandler
     * @param array          $rows
     *
     * @return string
     */
    protected function renderMatrixTable(ResultMetadata $resultHandler, array $rows)
    {
        $prepared = $this->prepareMatrixTable($resultHandler, $rows);

        $select = $resultHandler->getSelectColumns();
        $first  = reset($select);
        if (count($select) == 1 && in_array($first['renderer'], ['number', 'numberraw'], true)) {
            $totalType = $first['renderer'];
        } else {
            $totalType = false;
        }

        return $this->renderTableWrapper(
            $this->renderMatrixHeader($resultHandler, $prepared, $totalType).$this->renderMatrixBody($resultHandler, $prepared, $totalType),
            'matrix'
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
    protected function renderTableWrapper($inner, $extraClass = '')
    {
        return "<table class=\"report-builder-table $extraClass\" cellspacing=\"0\">\n$inner\n</table>\n";
    }

    /**
     * Renders the header row (for a simple table).
     *
     * @param ResultMetadata $resultHandler
     *
     * @return string
     */
    protected function renderHeader(ResultMetadata $resultHandler)
    {
        $columnHtml = [];
        foreach ($resultHandler->getGroupYColumns() as $column) {
            $columnHtml[] = '<th>'.$this->valueRenderer->escapeValue($column['title']).'</th>';
        }
        foreach ($resultHandler->getSelectColumns() as $column) {
            $columnHtml[] = '<th>'.$this->valueRenderer->escapeValue($column['title']).'</th>';
        }

        if ($this->withRollup($resultHandler)) {
            $columnHtml[] = '<th>Total</th>';
        }

        return '<thead><tr class="row-header">'.implode("\n\t", $columnHtml).'</tr></thead>';
    }

    /**
     * Renders the body of a "simple" table.
     *
     * @param ResultMetadata $resultHandler
     * @param array          $rows
     *
     * @return string
     */
    protected function renderBody(ResultMetadata $resultHandler, array $rows)
    {
        $groupColumns  = $resultHandler->getGroupYColumns();
        $selectColumns = $resultHandler->getSelectColumns();
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

                    $padding = ($resultHandler->hasFlag(ResultMetadata::FLAG_HIERARCHICAL) && empty($cells))
                        ? $this->getRowPadding($row)
                        : '';
                    $rendered = $padding.$this->renderCellValue($row, $groupColumn);
                    $cells[]  = "<th$rowSpan>{$rendered}</th>";
                }
            }

            foreach ($selectColumns as $column) {
                $padding = ($resultHandler->hasFlag(ResultMetadata::FLAG_HIERARCHICAL) && empty($cells))
                    ? $this->getRowPadding($row)
                    : '';
                $rendered = $padding.$this->renderCellValue($row, $column);
                $cells[]  = '<td>'.$rendered.'</td>';
            }
            if ($this->withRollup($resultHandler)) {
                $cells[] = '<td>'.$this->renderCellValue($row, 'hierarchy_rollup_count').'</td>';
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

    /**
     * @param ResultMetadata $resultHandler
     *
     * @return bool
     */
    protected function withRollup(ResultMetadata $resultHandler)
    {
        return $resultHandler->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)
        && $resultHandler->hasFlag(ResultMetadata::FLAG_WITH_ROLLUP);
    }

    /**
     * Renders the header rows of a matrix table.
     *
     * @param ResultMetadata $resultHandler
     * @param array          $prepared      Prepared matrix data (see _prepareMatrixTable)
     * @param bool|string    $totalType     If non empty, shows a total for each row/column
     *
     * @return string
     */
    protected function renderMatrixHeader(ResultMetadata $resultHandler, array $prepared, $totalType = false)
    {
        $rowSkipCount = count($resultHandler->getGroupXColumns());
        $colSkipCount = count($resultHandler->getGroupYColumns());

        $header = $this->renderMatrixHeaderRecur(['root'], $prepared['xDistinct']);
        $rows   = $header['depth'];
        ksort($rows);

        $output = [];

        $row = [];
        if ($colSkipCount) {
            $row[] = '<th'.($colSkipCount > 1 ? " colspan=\"$colSkipCount\"" : '').'>&nbsp;</th>';
        }
        $parts = [];
        foreach ($resultHandler->getGroupXColumns() as $column) {
            $parts[] = $column['title'];
        }
        $row[] = '<th colspan="'.$header['colSpan'].'" class="label">'
            .$this->valueRenderer->escapeValue(implode(' / ', $parts))
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
                    foreach ($resultHandler->getGroupYColumns() as $column) {
                        $prefix .= '<th'.$rowSpan.' class="label">'.$this->valueRenderer->escapeValue($column['title']).'</th>';
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
     * @param ResultMetadata $resultHandler
     * @param array          $rows
     *
     * @return string
     */
    protected function renderFooter(ResultMetadata $resultHandler, array $rows)
    {
        $totalColumns = $resultHandler->getTotalColumns();
        if (count($rows) < 2 || !$totalColumns) {
            return '';
        }

        $cells         = [];
        $groupYColumns = $resultHandler->getGroupYColumns();
        $selectColumns = $resultHandler->getSelectColumns();

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
                $cells[] = '<td>'.$this->renderCellValue($fakeRow, $column).'</td>';
            } else {
                $cells[] = '<td>&nbsp;</td>';
            }
        }

        return '<tfoot><tr class="row-body total-row">'.implode('', $cells).'</tr></tfoot>';
    }

    /**
     * Renders the body of a matrix table.
     *
     * @param ResultMetadata $resultHandler
     * @param array          $prepared      Prepared matrix data
     * @param bool|string    $totalType     If non empty, shows a total for each row/column
     *
     * @return string
     */
    protected function renderMatrixBody(ResultMetadata $resultHandler, array $prepared, $totalType = false)
    {
        if (!$prepared['yDistinct']) {
            // no Y grouping - that means we can have one row so fake it
            $rowKeys = ['root' => ''];
        } else {
            $rowKeys = $this->getMatrixRowGroups(['root'], $prepared['yDistinct']);
        }

        $matrixPaths = $this->getFinalMatrixPaths(['root'], $prepared['xDistinct']);
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
                $cells[] = '<td class="column-total">'.$this->valueRenderer->renderValue($rowTotal, $totalType).'</td>';
            }

            ++$rowCount;
            $class = ($rowCount % 2 ? 'odd' : 'even');

            $rows[] = '<tr class="row-body '.$class.'">'.$html.implode('', $cells).'</tr>';
        }

        if ($totalType && $resultHandler->getGroupYColumns()) {
            $cells   = [];
            $cells[] = '<th colspan="'.count($resultHandler->getGroupYColumns()).'">Total</th>';
            foreach ($columnTotals as $value) {
                $cells[] = '<td>'.$this->valueRenderer->renderValue($value, $totalType).'</td>';
            }
            $cells[] = '<td class="column-total">'.$this->valueRenderer->renderValue(array_sum($columnTotals), $totalType).'</td>';

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
    protected function renderMatrixHeaderRecur(array $path, array $distinctValues, $depth = 0)
    {
        $pathLookup = $this->getGroupPathKey($path);
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

            $child = $this->renderMatrixHeaderRecur($localPath, $distinctValues, $nextDepth);

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
     * @param array $row
     *
     * @return string
     */
    protected function getRowPadding(array $row)
    {
        $padding = '';
        if (array_key_exists('hierarchy_depth', $row) && ($depth = $row['hierarchy_depth'])) {
            $padding = str_repeat('&nbsp;', 4 * $depth).'&#8209;&#8209;&nbsp;';
        }

        return $padding;
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
    protected function getMatrixRowGroups(array $path, array $yDistinct)
    {
        $pathString = $this->getGroupPathKey($path);
        if (!isset($yDistinct[$pathString])) {
            return [];
        }

        $output = [];
        foreach ($yDistinct[$pathString] as $groupValue => $printValue) {
            $localPath   = $path;
            $localPath[] = $groupValue;

            $children = $this->getMatrixRowGroups($localPath, $yDistinct);
            if (!$children) {
                $output[$this->getGroupPathKey($localPath)] = '<th>'.$printValue.'</th>';
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
     * Gets the final path keys to a set of distinct values in a matrix table.
     *
     * @param array $path     Grouping paths
     * @param array $distinct Distinct values
     *
     * @return array List of path keys
     */
    protected function getFinalMatrixPaths(array $path, array $distinct)
    {
        $pathString = $this->getGroupPathKey($path);
        if (!isset($distinct[$pathString])) {
            return [];
        }

        $output = [];
        foreach ($distinct[$pathString] as $value => $null) {
            $localPath   = $path;
            $localPath[] = $value;

            $children = $this->getFinalMatrixPaths($localPath, $distinct);
            if (!$children) {
                $output[] = $this->getGroupPathKey($localPath);
            } else {
                $output = array_merge($output, $children);
            }
        }

        return $output;
    }
}
