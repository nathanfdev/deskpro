<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy\HierarchyPlugin;
use DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\PluginInterface;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Doctrine\DBAL\Connection;

/**
 * Class SqlSelectContext.
 */
class SqlSelectContext
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ResultMetadata
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
     * Constructor.
     *
     * @param Connection               $connection
     * @param ResultMetadata           $handler
     * @param Plugin\PluginInterface[] $plugins
     */
    public function __construct(Connection $connection, ResultMetadata $handler, array $plugins)
    {
        $this->connection = $connection;
        $this->handler    = $handler;
        $this->plugins    = $plugins;
    }

    /**
     * Executes an SqlSelect instance.
     *
     * @param SqlSelect      $sql
     * @param ResultMetadata $metadata
     *
     * @throws \Exception
     *
     * @return array
     */
    public function execute(SqlSelect $sql, ResultMetadata $metadata)
    {
        $pluginsCount = count($this->plugins);

        for ($i = 0; $i < $pluginsCount; ++$i) {
            $this->plugins[$i]->provide($sql);
            $this->plugins[$i]->beforeQuery();
        }

        $results = $this->connection->executeQuery($sql->toSql())->fetchAll(\PDO::FETCH_NUM);

        for ($i = $pluginsCount - 1; $i >= 0; --$i) {
            $results = $this->plugins[$i]->afterQuery($results, $metadata);
            $this->plugins[$i]->resultHandlerCallback($this->handler, $results);
        }

        return $results;
    }

    /**
     * @throws DpqlException
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
            throw new DpqlException('The HierarchyPlugin is not enabled');
        }

        return $this->hierarchyPlugin;
    }
}
