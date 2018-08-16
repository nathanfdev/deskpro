<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class JsonTableRenderer.
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

        if ($metadata->getGroupXColumns() && !$metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)) {
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
        $groupX   = $resultHandler->getGroupXColumns();
        $groupY   = $resultHandler->getGroupYColumns();
        $first    = reset($select);
        if (count($select) == 1 && in_array($first['renderer'], ['number', 'numberraw'], true)) {
            $totalType = $first['renderer'];
        } else {
            $totalType = false;
        }

        $columnsHead = [];
        if ($groupY) {
            foreach ($groupY as $column) {
                $columnsHead[] = $column['title'];
            }
        } else {
            $columnsHead[] = '';
        }

        $rowsData = [];

        $rowGroups = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['yDistinct']);
        if (!$rowGroups) {
            // need to fake it so we get a row with no Y grouping
            $rowGroups = ['root' => []];
        }
        $headerCols = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['xDistinct']);
        $totalRow   = ['Total'];
        $rowsGroup  = [];
        $totalData  = count(reset($rowGroups));

        if ($totalData > 1) {
            for ($i = 0; $i < $totalData - 1; ++$i) {
                $rowsGroup[] = $i;
            }
        }

        // getting through rows
        foreach ($rowGroups as $yPath => $rowHead) {
            if (count($rowHead) == 1) {
                $rowData = [reset($rowHead) ?: 'None'];
            } else {
                $rowData = [];
                foreach ($rowHead as $headData) {
                    $rowData[] = $headData ?: 'None';
                }
            }

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
            if ($totalData > 1) {
                $totalRow = array_pad($totalRow, -1 * (count($totalRow) + $totalData - 1), '');
            }

            $rowsData[] = array_values($totalRow);
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
            if ($total < 1 && $index > $totalData - 1) {
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

        $result = [
            'columns' => array_values($columnsHead),
            'data'    => $finalRows,
        ];

        if ($rowsGroup) {
            $result['rowsGroup'] = $rowsGroup;
        }

        return $result;
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
        if ($resultHandler->hasFlag(ResultMetadata::FLAG_WITH_ROLLUP)) {
            $columns[] = 'Total';
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

        foreach ($rows as $rowId => $row) {
            $cells = [];

            if ($groupColumns) {
                $groupValues = [];
                foreach ($groupColumns as $groupId => $groupColumn) {
                    $groupValues[$groupId] = $this->getColumnValue($row, $groupColumn['groupResultId']);
                }

                foreach ($groupColumns as $groupId => $groupColumn) {
                    $padding = ($metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL) && empty($cells))
                        ? $this->getRowPadding($row)
                        : '';
                    $cells[] = $padding.$this->renderCellValue($row, $groupColumn, $metadata);
                }
            }

            foreach ($selectColumns as $column) {
                $padding = ($metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL) && empty($cells))
                    ? $this->getRowPadding($row)
                    : '';
                $cells[] = $padding.$this->renderCellValue($row, $column, $metadata);
            }
            if ($metadata->hasFlag(ResultMetadata::FLAG_WITH_ROLLUP)) {
                $cells[] = $this->renderCellValue($row, 'hierarchy_rollup_count', $metadata);
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

    private function getRowPadding(array $row)
    {
        $padding = '';
        if (array_key_exists('hierarchy_depth', $row) && ($depth = $row['hierarchy_depth'])) {
            $padding = str_repeat('&nbsp;', 4 * $depth).'&#8209;&#8209;&nbsp;';
        }

        return $padding;
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

    /**
     * {@inheritdoc}
     */
    public function mergeResults(array $results, $graphType, array $options)
    {
        return reset($results);
    }
}
