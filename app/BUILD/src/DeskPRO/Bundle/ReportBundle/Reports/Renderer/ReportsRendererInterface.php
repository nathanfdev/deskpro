<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use DeskPRO\Bundle\ReportBundle\Reports\Results;

/**
 * Interface ReportsRendererInterface.
 */
interface ReportsRendererInterface
{
    const TYPE_TABLE  = 'table';
    const TYPE_LINE   = 'line';
    const TYPE_PIE    = 'pie';
    const TYPE_BAR    = 'bar';
    const TYPE_AREA   = 'area';
    const TYPE_GAUGE  = 'gauge';
    const TYPE_BUBBLE = 'bubble';
    const TYPE_STAT   = 'stat';

    /**
     * @return string
     */
    public static function getOutputFormat();

    /**
     * Gets the MIME content type for this type of output.
     *
     * @return string
     */
    public static function getContentType();

    /**
     * Gets the file extension for this type of output.
     *
     * @return string
     */
    public static function getExtension();

    /**
     * Render to the specified format and type.
     *
     * @param Results $results
     * @param array   $options
     *
     * @return string|array
     */
    public function render(Results $results, array $options = []);

    /**
     * Renders the value for a specific cell.
     *
     * @param mixed [int]    $row
     * @param mixed          $column
     * @param ResultMetadata $metadata
     * @param bool           $useRenderer
     *
     * @return string
     */
    public function renderCellValue(array $row, $column, ResultMetadata $metadata, $useRenderer = null);

    /**
     * Merge layered results.
     *
     * @param array $results
     * @param array $options
     *
     * @return mixed
     */
    public function mergeResults(array $results, array $options);
}
