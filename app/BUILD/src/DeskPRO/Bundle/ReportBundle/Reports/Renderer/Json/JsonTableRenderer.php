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
 * Class JsonChartRenderer.
 */
class JsonTableRenderer extends AbstractJsonRenderer
{
    /**
     * Constructor.
     *
     * @param JsonValueRenderer $valueRenderer
     */
    public function __construct(JsonValueRenderer $valueRenderer)
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
            return;
        }

        if ($metadata->getGroupXColumns()) {
            return $this->renderMatrixTable($metadata, $rows, $options);
        }

        return [
            'columns' => $this->renderHeader($metadata, $options),
            'data'    => $this->renderBody($metadata, $rows, $options),
        ];
    }

    /**
     * Renders a matrix table (with X and Y grouping).
     *
     * @param ResultMetadata $resultHandler
     * @param array          $rows
     * @param array          $options
     *
     * @return array
     */
    protected function renderMatrixTable(ResultMetadata $resultHandler, array $rows, array $options = [])
    {
        // matrix table - X() values translate to bottom axis, each row (from Y()) is a new line/stack.
        $prepared = $this->prepareMatrixTable($resultHandler, $rows);
        $lookup   = $prepared['lookup'];
        $select   = $resultHandler->getSelectColumns();
        $first    = reset($select);
        if (count($select) == 1 && in_array($first['renderer'], ['number', 'numberraw'], true)) {
            $totalType = $first['renderer'];
        } else {
            $totalType = false;
        }

        $columnsHead = [''];
        $rowsData    = [];

        $rowGroups = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['yDistinct']);
        if (!$rowGroups) {
            // need to fake it so we get a row with no Y grouping
            $rowGroups = ['root' => []];
        }
        $headerCols = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['xDistinct']);
        $totalRow   = ['Total'];

        // getting through rows
        foreach ($rowGroups as $yPath => $rowHead) {
            $rowData  = [reset($rowHead) ?: 'None'];
            $rowTotal = 0;
            foreach ($headerCols as $xPath => $printable) {
                if (isset($lookup[$yPath][$xPath])) {
                    $value = $this->filterGraphValue($lookup[$yPath][$xPath]);
                } else {
                    $value = '';
                }
                $rowData[] = $value;
                if ($totalType) {
                    $rowTotal += (float) str_replace(',', '', $value);
                    if (!isset($totalRow[$xPath])) {
                        $totalRow[$xPath] = 0;
                    }
                    $totalRow[$xPath] += (float) str_replace(',', '', $value);
                }
            }
            if ($totalType) {
                $rowData[] = $rowTotal;
            }
            $rowsData[] = $rowData;
        }
        // URGH, need to be refactored ASAP
        foreach ($headerCols as $colName) {
            $h             = reset($colName);
            $columnsHead[] = $h ?: 'None';
        }
        if ($totalType) {
            $columnsHead[] = 'Total';
            $totalRow[]    = '';
            $rowsData[]    = array_values($totalRow);
        }

        $rows = [];
        foreach ($rowsData as $index => $rowData) {
            if (array_sum($rowData) > 1) {
                $rows[] = $rowData;
            }
        }
        $cols = [];

        foreach ($rows as $row) {
            foreach ($row as $index => $value) {
                if (!isset($cols[$index])) {
                    $cols[$index] = 0;
                }
                $cols[$index] += (int) $value;
            }
        }

        foreach ($cols as $index => $total) {
            if ($total < 1 && $index > 0) {
                foreach ($rows as &$rowData) {
                    unset($rowData[$index]);
                }
                unset($columnsHead[$index]);
            }
        }
        $finalRows = [];
        foreach ($rows as $row) {
            $finalRows[] = array_values($row);
        }

        return [
            'columns' => $columnsHead,
            'data'    => $finalRows,
        ];
    }

    /**
     * Renders the header row (for a simple table).
     *
     * @param ResultMetadata $resultHandler
     * @param array          $options
     *
     * @return array
     */
    protected function renderHeader(ResultMetadata $resultHandler, array $options = [])
    {
        $columns = [];
        if (empty($options['noGroupingColumn'])) {
            foreach ($resultHandler->getGroupYColumns() as $column) {
                $columns[] = $this->valueRenderer->escapeValue($column['title']);
            }
        }
        foreach ($resultHandler->getSelectColumns() as $column) {
            $columns[] = $this->valueRenderer->escapeValue($column['title']);
        }

        return $columns;
    }

    /**
     * Renders the body of a "simple" table.
     *
     * @param ResultMetadata $metadata
     * @param array          $rows
     * @param array          $options
     *
     * @return array
     */
    protected function renderBody(ResultMetadata $metadata, array $rows, array $options = [])
    {
        if (empty($options['noGroupingColumn'])) {
            $groupColumns = $metadata->getGroupYColumns();
        } else {
            $groupColumns = [];
        }

        $selectColumns = $metadata->getSelectColumns();
        $rows          = array_values($rows); // need continuous keys

        $rowsRendered = [];
        $rowCount     = 0;

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
                    $rendered = $this->renderCellValue($row, $groupColumn, $metadata);

                    $cells[] = "<th$rowSpan>$rendered</th>";
                }
            }

            foreach ($selectColumns as $column) {
                $cells[] = $this->renderCellValue($row, $column, $metadata);
            }

            ++$rowCount;
            $rowsRendered[] = $cells;
        }

        if ($rowsRendered) {
            return $rowsRendered;
        } else {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function implodeSplitOutput(array $output)
    {
        $return = ['data' => []];
        foreach ($output as $outputItem) {
            $return['columns'] = $outputItem['columns'];
            $return['data']    = array_merge($return['data'], $outputItem['data']);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function renderSplitOutputWithHeader($header, $body)
    {
        return $body;
    }

    public function mergeResults(array $results, array $options)
    {
        return reset($results);
    }
}
