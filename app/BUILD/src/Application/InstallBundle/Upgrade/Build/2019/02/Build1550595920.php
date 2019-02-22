<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1550595920 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $sql = <<<'SQL'
SELECT id
  FROM tickets
  WHERE status = 'hidden'
  AND hidden_status = 'spam'
  ORDER BY ID DESC
  LIMIT 10000
SQL;

        $ids = $this->getDbConnection()->fetchAllCol($sql);
        $this->getDbConnection()->insert('tmp_data', [
            'name'         => 'ticket_ids',
            'auth'         => 1543332886,
            'data'         => json_encode($ids, \JSON_PRETTY_PRINT),
            'date_created' => date('Y-m-d H:i:s'),
            'date_expire'  => date('Y-m-d H:i:s', time() + 86400),
        ]);
    }
}
