<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Plugin\Hierarchy\HierarchyPlugin;
use Application\DeskPRO\Dpql\Plugin\PluginInterface;

/**
 * Class SqlSelectContext.
 */
class SqlSelectContext
{
    /**
     * @var ResultHandler
     */
    private $handler;

    /**
     * @var PluginInterface[]
     */
    private $plugins;

    /**
     * @var HierarchyPlugin
     */
    private $hierarchyPlugin;

    /**
     * @param ResultHandler            $handler
     * @param Plugin\PluginInterface[] $plugins
     */
    public function __construct(ResultHandler $handler, array $plugins)
    {
        $this->handler = $handler;
        $this->plugins = $plugins;
    }

    /**
     * Executes an SqlSelect instance.
     *
     * @param SqlSelect $sql
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     *
     * @return array
     */
    public function execute(SqlSelect $sql)
    {
        $pluginsCount = count($this->plugins);

        for ($i = 0; $i < $pluginsCount; ++$i) {
            $this->plugins[$i]->provide($sql);
            $this->plugins[$i]->beforeQuery();
        }

        $results = App::getDbRead('reports')->executeQuery($sql->toSql())->fetchAll(\PDO::FETCH_NUM);

        for ($i = $pluginsCount - 1; $i >= 0; --$i) {
            $results = $this->plugins[$i]->afterQuery($results);
            $this->plugins[$i]->resultHandlerCallback($this->handler, $results);
        }

        return $results;
    }

    /**
     * @throws Exception
     *
     * @return HierarchyPlugin|PluginInterface
     */
    public function getHierarchyPlugin()
    {
        if (!$this->hierarchyPlugin) {
            foreach ($this->plugins as $plugin) {
                if ($plugin instanceof HierarchyPlugin) {
                    $this->hierarchyPlugin = $plugin;
                    break;
                }
            }
        }

        if (!$this->hierarchyPlugin) {
            throw new Exception('The HierarchyPlugin is not enabled');
        }

        return $this->hierarchyPlugin;
    }
}
