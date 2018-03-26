<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Plugin;

use Application\DeskPRO\Dpql\ResultHandler;
use Application\DeskPRO\Dpql\SqlSelect;

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
     * @param array $results
     *
     * @return array Modified results
     */
    public function afterQuery(array $results);

    /**
     * @param ResultHandler $handler
     * @param array         $results
     */
    public function resultHandlerCallback(ResultHandler $handler, array $results);
}
