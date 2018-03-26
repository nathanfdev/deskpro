<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817824 extends AbstractBuild
{
    public function run()
    {
        $this->out('Populate deparment triggers with api by_user_mode');
        $connection = $this->getDbConnection('default');
        $sql        = <<<'SQL'
        SELECT * FROM `ticket_triggers` 
        WHERE `department_id` IS NOT NULL
        AND `by_user_mode` NOT LIKE "%api%"
SQL;

        $triggersWithoutApiMode = $connection->fetchAll($sql);
        $statement              = $connection->prepare('UPDATE `ticket_triggers` SET `by_user_mode` = :modes WHERE `id` = :id');
        foreach ($triggersWithoutApiMode as $trigger) {
            $modes = $trigger['by_user_mode'];
            $modes = explode(',', $modes);
            array_unshift($modes, 'api');
            array_map(function (&$item) {
                $item = trim($item);
            }, $modes);
            $statement->execute(['id' => $trigger['id'], 'modes' => implode(',', $modes)]);
        }
    }
}
