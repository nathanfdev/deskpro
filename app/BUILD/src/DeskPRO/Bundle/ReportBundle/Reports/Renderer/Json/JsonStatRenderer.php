<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer\Json;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Class JsonStatRenderer.
 */
class JsonStatRenderer extends AbstractJsonRenderer
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
        return self::TYPE_STAT;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRender(array $rows, ResultMetadata $metadata, array $options = [])
    {
        if (!$rows) {
            return;
        }

        $return = [
            'value'       => $this->renderValue($metadata, $rows),
            'description' => $this->renderDescription($metadata, $rows),
        ];

        if (!$return['value']) {
            return;
        }

        return $this->extractClickUrlVars($rows, $metadata, $return);
    }

    /**
     * @param array          $rows
     * @param ResultMetadata $metadata
     * @param array          $result
     *
     * @return mixed
     */
    private function extractClickUrlVars(array $rows, ResultMetadata $metadata, array $result)
    {
        $selectColumns = $metadata->getSelectColumns();
        foreach ($selectColumns as $selectColumn) {
            if (strpos($selectColumn['title'], '__var') !== false) {
                $result[$selectColumn['title']] = $this->renderCellValue($rows[0], $selectColumn, $metadata);
            }
        }

        return $result;
    }

    /**
     * Renders the header row (for a simple table).
     *
     * @param ResultMetadata $metadata
     * @param array          $rows
     *
     * @return string
     */
    protected function renderValue(ResultMetadata $metadata, array $rows)
    {
        $unitLeft  = '';
        $unitRight = '';

        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'unit_left') {
                $unitLeft = $this->renderCellValue($rows[0], $column, $metadata);
            }
            if ($column['title'] === 'unit_right') {
                $unitLeft = $this->renderCellValue($rows[0], $column, $metadata);
            }
        }

        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_value') {
                return $unitLeft.$this->renderCellValue($rows[0], $column, $metadata).$unitRight;
            }
        }

        return '0';
    }

    /**
     * @param ResultMetadata $metadata
     * @param array          $rows
     *
     * @return string
     */
    protected function renderDescription(ResultMetadata $metadata, array $rows)
    {
        foreach ($metadata->getSelectColumns() as $column) {
            if ($column['title'] === 'stat_description') {
                return $this->renderCellValue($rows[0], $column, $metadata);
            }
        }

        return '';
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
    protected function renderCellValue(array $row, $column, ResultMetadata $metadata, $useRenderer = null)
    {
        $value = $column['resultId'] ? $row[$column['resultId'] - 1] : '';

        if ($useRenderer) {
            $renderer = $useRenderer;
        } else {
            $renderer = is_array($column) && array_key_exists('renderer', $column) ? $column['renderer'] : 'string';
        }
        if ($renderer instanceof \Closure) {
            /* @var $renderer \Closure */

            return $renderer($this->valueRenderer, $value, $row, $this, $metadata);
        }

        return $this->valueRenderer->renderValue($value, $renderer, $metadata);
    }

    /**
     * @param array $results
     * @param array $options
     *
     * @return mixed
     */
    public function mergeResults(array $results, array $options)
    {
        return reset($results);
    }
}
