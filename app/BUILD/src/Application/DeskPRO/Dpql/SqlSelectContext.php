<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Plugin\PluginInterface;

/**
 * Class SqlSelectContext.
 */
class SqlSelectContext
{
    /**
     * @var PluginInterface[]
     */
    private $plugins;

    /**
     * @param Plugin\PluginInterface[] $plugins
     */
    public function __construct(array $plugins)
    {
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
        try {
            $pluginsCount = count($this->plugins);

            for ($i = 0; $i < $pluginsCount; ++$i) {
                $this->plugins[$i]->provide($sql);
                $this->plugins[$i]->beforeQuery();
            }

            $results = App::getDbRead('reports')->executeQuery($sql->toSql())->fetchAll(\PDO::FETCH_NUM);

            for ($i = $pluginsCount - 1; $i >= 0; --$i) {
                $results = $this->plugins[$i]->afterQuery($results);
            }

            return $results;
        } catch (\Exception $e) {
            die('Exception: '.$e->getMessage()."\n\n".$e->getTraceAsString());
        }
    }
}
