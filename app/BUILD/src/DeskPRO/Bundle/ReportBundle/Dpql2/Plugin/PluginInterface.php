<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Plugin;

use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Interface PluginInterface.
 *
 * SqlSelectContext plugin allows to hook into the DPQL processing at different points and add or modify various
 * features.
 */
interface PluginInterface
{
    /**
     * @param SqlSelect $sql
     */
    public function provide(SqlSelect $sql);

    /**
     * @return mixed
     */
    public function beforeQuery();

    /**
     * @param array          $results
     * @param ResultMetadata $metadata
     *
     * @return array Modified results
     */
    public function afterQuery(array $results, ResultMetadata $metadata);

    /**
     * @param ResultMetadata $handler
     * @param array          $results
     */
    public function resultHandlerCallback(ResultMetadata $handler, array $results);
}
