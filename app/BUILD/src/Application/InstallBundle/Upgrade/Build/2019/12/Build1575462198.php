<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1575462198 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        if (defined('DPC_IS_CLOUD')) {
            $count = $this->getDbConnection('default')->fetchColumn("SELECT COUNT(*) FROM api_log");
            if ($count > 5000) {
                // truncate to make faster
                $this->getDbConnection('default')->exec("TRUNCATE TABLE api_log");
            }
        }

        $sh = $this->getSchemaHelper();
        $fk = $sh->findForeignKey('api_log', 'api_key_id', 'api_keys', 'id');
        if ($fk) {
            $this->execDbQuery('default', "ALTER TABLE api_log DROP FOREIGN KEY `{$fk->getName()}`");
        }
        $this->execDbQuery('default', "ALTER TABLE api_log ADD CONSTRAINT FK_CCBD2EF18BE312B3 FOREIGN KEY (api_key_id) REFERENCES api_keys (id) ON DELETE SET NULL");
    }

    public function run()
    {
    }
}
