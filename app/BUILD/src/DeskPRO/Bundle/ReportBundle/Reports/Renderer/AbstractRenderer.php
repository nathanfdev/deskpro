<?php

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
        return $this->doRender($results->getResults(), $results->getMetadata(), $options);
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
        if (isset($id['resultId']) && is_array($id['resultId'])) {
            $value = [];
            foreach ($id['resultId'] as $resultId) {
                $value[] = $resultId ? $row[$resultId - 1] : '';
            }

            return implode('', $value);
        }

        if (isset($id['resultId'])) {
            $index = $id['resultId'] - 1;
        } elseif (is_string($id) && !ctype_digit($id)) {
            $index = $id;
        } else {
            $index = $id - 1;
        }

        return $id && array_key_exists($index, $row) ? $row[$index] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function renderCellValue(array $row, $column, ResultMetadata $metadata, $useRenderer = null)
    {
        if (is_string($column)) {
            $value = array_key_exists($column, $row) ? $row[$column] : '';
        } else {
            if (is_array($column['resultId'])) {
                $value = [];
                foreach ($column['resultId'] as $resultId) {
                    $value[] = $resultId ? $row[$resultId - 1] : '';
                }
            } else {
                $value = $column['resultId'] && isset($row[$column['resultId'] - 1]) ? $row[$column['resultId'] - 1] : '';
            }
        }

        if ($useRenderer) {
            $renderer = $useRenderer;
        } else {
            $renderer = is_array($column) && array_key_exists('renderer', $column) ? $column['renderer'] : null;
        }
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

    /**
     * {@inheritdoc}
     */
    public function mergeResults(array $results, array $options)
    {
        return implode('PHP_EOL', $results);
    }
}
