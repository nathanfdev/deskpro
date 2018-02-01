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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class CsvTableRenderer.
 */
class CsvTableRenderer extends AbstractRenderer
{
    /**
     * Constructor.
     *
     * @param TextValueRenderer $valueRenderer
     */
    public function __construct(TextValueRenderer $valueRenderer)
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
    public static function getContentType()
    {
        return 'text/csv';
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtension()
    {
        return 'csv';
    }

    /**
     * {@inheritdoc}
     */
    public function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return '';
        }

        if ($metadata->getGroupXColumns() && !$metadata->hasFlag(ResultMetadata::FLAG_HIERARCHICAL)) {
            return $this->renderMatrixTable($metadata, $rows);
        }

        $groupYColumns = $metadata->getGroupYColumns();
        $selectColumns = $metadata->getSelectColumns();

        $output = [];

        $columns = [];
        foreach ($groupYColumns as $column) {
            $columns[] = $this->wrapCell($column['title']);
        }
        foreach ($selectColumns as $column) {
            $columns[] = $this->wrapCell($column['title']);
        }

        $output[] = implode(',', $columns);

        foreach ($rows as $row) {
            $columns = [];

            foreach ($groupYColumns as $column) {
                $columns[] = $this->wrapCell($this->renderCellValue($row, $column, $metadata));
            }
            foreach ($selectColumns as $column) {
                $columns[] = $this->wrapCell($this->renderCellValue($row, $column, $metadata));
            }

            $output[] = implode(',', $columns);
        }

        // output a row of totals if requested - only do it with grouping, as otherwise there's
        // no real way of marking a row as the totals and that'd be confusing
        $totalColumns = $metadata->getTotalColumns();
        if (count($rows) > 1 && $totalColumns && $groupYColumns) {
            $cells = [];

            if ($groupYColumns) {
                foreach ($groupYColumns as $column) {
                    $cells[] = $this->wrapCell('');
                }
                array_pop($cells);
                $cells[] = $this->wrapCell('Total');
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
                    $columnTotals[$id] += (float) $this->getColumnValue($row, $id);
                }
            }

            $firstRow = reset($rows);
            $fakeRow  = array_fill_keys(array_keys($firstRow), null);
            foreach ($columnTotals as $id => $value) {
                $fakeRow[$id - 1] = $value;
            }

            foreach ($selectColumns as $column) {
                if (isset($columnTotals[$column['resultId']])) {
                    $cells[] = $this->wrapCell($this->renderCellValue($fakeRow, $column, $metadata));
                } else {
                    $cells[] = $this->wrapCell('');
                }
            }
            $output[] = implode(',', $cells);
        }

        return implode("\r\n", $output);
    }

    /**
     * Renders a matrix table (with X and Y grouping).
     *
     * @param ResultMetadata $metadata
     * @param array          $rows
     *
     * @return string
     */
    protected function renderMatrixTable(ResultMetadata $metadata, array $rows)
    {
        $prepared = $this->prepareMatrixTable($metadata, $rows);
        $lookup   = $prepared['lookup'];

        $rows = [];

        $rowGroups  = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['yDistinct']);
        $headerCols = $this->getFinalMatrixPathsWithPrintable(['root'], $prepared['xDistinct']);

        $select = $metadata->getSelectColumns();
        $first  = reset($select);
        if (count($select) == 1 && in_array($first['renderer'], ['number', 'numberraw'], true)) {
            $totalType = $first['renderer'];
        } else {
            $totalType = false;
        }

        $headerRow = [];
        foreach ($metadata->getGroupYColumns() as $column) {
            $headerRow[] = $this->wrapCell('');
        }
        $parts = [];
        foreach ($metadata->getGroupXColumns() as $column) {
            $parts[] = $column['title'];
        }
        $headerRow[] = $this->wrapCell(implode(' / ', $parts));
        foreach ($headerCols as $headerCol) {
            $headerRow[] = $this->wrapCell('');
        }
        array_pop($headerRow);
        if ($totalType) {
            $headerRow[] = $this->wrapCell('');
        }

        $rows[] = implode(',', $headerRow);

        $headerRow = [];
        foreach ($metadata->getGroupYColumns() as $column) {
            $headerRow[] = $this->wrapCell($column['title']);
        }
        foreach ($headerCols as $headerCol) {
            $headerRow[] = $this->wrapCell(implode(' / ', $headerCol));
        }

        if ($totalType) {
            $headerRow[] = $this->wrapCell('Total');
        }

        $rows[] = implode(',', $headerRow);

        if (!$rowGroups) {
            // need to fake it so we get a row with no Y grouping
            $rowGroups = ['root' => []];
        }

        $columnTotals = [];

        foreach ($rowGroups as $yPath => $printable) {
            $columns  = [];
            $rowTotal = 0;

            foreach ($printable as $print) {
                $columns[] = $this->wrapCell($print);
            }

            foreach ($headerCols as $xPath => $null) {
                if (isset($lookup[$yPath][$xPath])) {
                    $value = $lookup[$yPath][$xPath];
                } else {
                    $value = '';
                }
                $columns[] = $this->wrapCell($value);

                if ($totalType) {
                    $rowTotal += (float) str_replace(',', '', $value);
                    if (!isset($columnTotals[$xPath])) {
                        $columnTotals[$xPath] = 0;
                    }
                    $columnTotals[$xPath] += (float) str_replace(',', '', $value);
                }
            }

            if ($totalType) {
                $columns[] = $this->wrapCell($this->valueRenderer->renderValue($rowTotal, $totalType, $metadata));
            }

            $rows[] = implode(',', $columns);
        }

        if ($totalType && $metadata->getGroupYColumns()) {
            $columns = [];
            foreach ($metadata->getGroupYColumns() as $rowGroupSkip) {
                $columns[] = $this->wrapCell('');
            }
            array_pop($columns);
            $columns[] = $this->wrapCell('Total');
            foreach ($columnTotals as $value) {
                $columns[] = $this->wrapCell($this->valueRenderer->renderValue($value, $totalType, $metadata));
            }
            $columns[] = $this->wrapCell($this->valueRenderer->renderValue(array_sum($columnTotals), $totalType, $metadata));

            $rows[] = implode(',', $columns);
        }

        return implode("\r\n", $rows);
    }

    /**
     * {@inheritdoc}
     */
    protected function wrapCell($value)
    {
        $value = str_replace('"', '""', $value);

        return "\"$value\"";
    }

    /**
     * {@inheritdoc}
     */
    protected function implodeSplitOutput(array $output)
    {
        return implode("\r\n\r\n", $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderSplitOutputWithHeader($header, $body)
    {
        return "\"$header\"\r\n$body";
    }
}
