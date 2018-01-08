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

        $results = $this->connection->executeQuery($sql->toSql())->fetchAll(\PDO::FETCH_NUM);

        for ($i = $pluginsCount - 1; $i >= 0; --$i) {
            $results = $this->plugins[$i]->afterQuery($results);
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
