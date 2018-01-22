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
use DeskPRO\Bundle\ReportBundle\Reports\Results;

/**
 * Class AbstractRenderer.
 */
abstract class AbstractRenderer implements ReportsRendererInterface
{
    /**
     * @var AbstractValueRenderer
     */
    protected $valueRenderer;

    /**
     * {@inheritdoc}
     */
    public function render(Results $results, array $options = [])
    {
        $metadata     = $results->getMetadata();
        $splitColumns = $metadata->getSplitColumns();

        if ($splitColumns) {
            $output = [];
            foreach ($results->getSplitResults() as $splitResult) {
                $result = $this->doRender($splitResult[0], $results->getMetadata());
                if ($result) {
                    $splitPrint = [];
                    foreach ($metadata->getSplitColumns() as $splitColumn) {
                        $splitPrint[] = $this->renderCellValue($splitResult[1], $splitColumn, $results->getMetadata());
                    }

                    $output[] = $this->renderSplitOutputWithHeader(implode(' / ', $splitPrint), $result);
                }
            }

            return $this->implodeSplitOutput($output);
        } else {
            return $this->doRender($results->getResults(), $results->getMetadata());
        }
    }

    /**
     * Gets the value of a particular column for the given row.
     *
     * @param array $row
     * @param mixed $id
     *
     * @return string
     */
    public function getColumnValue(array $row, $id)
    {
        if (is_array($id) && isset($id['resultId'])) {
            $index = $id['resultId'] - 1;
        } elseif (is_string($id) && !ctype_digit($id)) {
            $index = $id;
        } else {
            $index = $id - 1;
        }

        return $id && array_key_exists($index, $row) ? $row[$index] : '';
    }

    /**
     * Renders the value for a specific cell.
     *
     * @param mixed [int]    $row
     * @param mixed          $column
     * @param ResultMetadata $metadata
     *
     * @return string
     */
    protected function renderCellValue(array $row, $column, ResultMetadata $metadata)
    {
        if (is_string($column)) {
            $value = array_key_exists($column, $row) ? $row[$column] : '';
        } else {
            $value = $column['resultId'] ? $row[$column['resultId'] - 1] : '';
        }

        $renderer = is_array($column) && array_key_exists('renderer', $column) ? $column['renderer'] : null;
        if ($renderer instanceof \Closure) {
            /* @var $renderer \Closure */

            return $renderer($this->valueRenderer, $value, $row, $this, $metadata);
        }

        return $this->valueRenderer->renderValue($value, $renderer, $metadata);
    }

    /**
     * Gets the final paths to a matrix row/column entry with the value being the printable
     * value that lead to that entry.
     *
     * @param array $path
     * @param array $distinctValues
     * @param array $printPath
     *
     * @return array
     */
    protected function getFinalMatrixPathsWithPrintable(array $path, array $distinctValues, array $printPath = [])
    {
        $pathLookup = $this->getGroupPathKey($path);
        if (!isset($distinctValues[$pathLookup])) {
            return [];
        }

        $output = [];

        foreach ($distinctValues[$pathLookup] as $key => $value) {
            $localPath   = $path;
            $localPath[] = $key;

            $localPrintPath   = $printPath;
            $localPrintPath[] = $value;

            $childOutput = $this->getFinalMatrixPathsWithPrintable($localPath, $distinctValues, $localPrintPath);
            if (!$childOutput) {
                // a leaf - responsible for output
                $output[$this->getGroupPathKey($localPath)] = $localPrintPath;
            } else {
                $output = array_merge($output, $childOutput);
            }
        }

        return $output;
    }

    /**
     * Gets the string key to identify a path to a value based on parts.
     *
     * @param array $groupParts
     *
     * @return string
     */
    protected function getGroupPathKey(array $groupParts)
    {
        return implode('|', $groupParts);
    }

    /**
     * Prepares data for a matrix table.
     *
     * Returns array with:
     *  - xDistinct[pathString][renderedValue] = true -- used to find distinct values over X grouping
     *  - yDistinct[pathString][renderedValue] = true -- used to find distinct values over Y grouping
     *  - lookup[yPath][xPath] = cell value -- value for cell at the y/x position specified
     *
     * @param ResultMetadata $metadata
     * @param array          $rows
     *
     * @return array
     */
    protected function prepareMatrixTable(ResultMetadata $metadata, array $rows)
    {
        $groupXColumns = $metadata->getGroupXColumns();
        $groupYColumns = $metadata->getGroupYColumns();
        $selectColumns = $metadata->getSelectColumns();

        $distinctXValues = [];
        $distinctXSort   = [];
        $distinctYValues = [];
        $distinctYSort   = [];
        $lookup          = [];

        foreach ($rows as $row) {
            $xPath = ['root'];
            foreach ($groupXColumns as $column) {
                $pathString = $this->getGroupPathKey($xPath);
                $groupValue = $this->getColumnValue($row, $column['groupResultId']);
                $rendered   = $this->renderCellValue($row, $column, $metadata);

                $distinctXValues[$pathString][$groupValue] = $rendered;
                $distinctXSort[$pathString][$groupValue]   = $groupValue === null ? null : $this->getColumnValue($row, $column);

                $xPath[] = $groupValue;
            }

            $yPath = ['root'];
            foreach ($groupYColumns as $column) {
                $pathString = $this->getGroupPathKey($yPath);
                $groupValue = $this->getColumnValue($row, $column['groupResultId']);
                $rendered   = $this->renderCellValue($row, $column, $metadata);

                $distinctYValues[$pathString][$groupValue] = $rendered;
                $distinctYSort[$pathString][$groupValue]   = $groupValue === null ? null : $this->getColumnValue($row, $column);

                $yPath[] = $groupValue;
            }

            $lookup[$this->getGroupPathKey($yPath)][$this->getGroupPathKey($xPath)] =
                $this->renderMatrixCell($row, $selectColumns, $metadata);
        }

        foreach ($distinctXSort as $path => $sortValues) {
            uasort($sortValues, 'strnatcasecmp');

            $values                 = $distinctXValues[$path];
            $distinctXValues[$path] = [];
            foreach ($sortValues as $key => $null) {
                $distinctXValues[$path][$key] = $values[$key];
            }
        }
        foreach ($distinctYSort as $path => $sortValues) {
            uasort($sortValues, 'strnatcasecmp');

            $values                 = $distinctYValues[$path];
            $distinctYValues[$path] = [];
            foreach ($sortValues as $key => $null) {
                $distinctYValues[$path][$key] = $values[$key];
            }
        }

        return [
            'xDistinct' => $distinctXValues,
            'yDistinct' => $distinctYValues,
            'lookup'    => $lookup,
        ];
    }

    /**
     * Renders a matrix cell.
     *
     * @param array          $row
     * @param array          $selectColumns
     * @param ResultMetadata $metadata
     *
     * @return string
     */
    protected function renderMatrixCell(array $row, array $selectColumns, ResultMetadata $metadata)
    {
        $values = [];
        foreach ($selectColumns as $column) {
            $values[] = $this->renderCellValue($row, $column, $metadata);
        }

        return implode(' / ', $values);
    }

    /**
     * Joins the already rendered output into one output.
     *
     * @param array $output
     *
     * @return string
     */
    abstract protected function implodeSplitOutput(array $output);

    /**
     * Finalizes the rendering of a split output by rendering the body with the header.
     *
     * @param string $header
     * @param string $body
     *
     * @return string
     */
    abstract protected function renderSplitOutputWithHeader($header, $body);

    /**
     * @param array          $rows
     * @param ResultMetadata $metadata
     * @param array          $options
     *
     * @return mixed
     */
    abstract protected function doRender(array $rows, ResultMetadata $metadata, array $options = []);
}
