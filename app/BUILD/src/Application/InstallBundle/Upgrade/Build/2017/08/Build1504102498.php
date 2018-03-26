<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1504102498 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'DROP INDEX state_unique ON app2_app_state_v2');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX state_unique ON app2_app_state_v2 (app_instance_id, name, entity_id, person_id)');
    }

    public function run()
    {
        $fixTrelloAuthSql = $this->getFixTrelloAuthSql();
        $this->execDbQuery('default', $fixTrelloAuthSql);

        $fixTrelloCardsSql = $this->getFixTrelloCardsPermissionsSQL();
        $this->execDbQuery('default', $fixTrelloCardsSql);
    }

    public function getFixTrelloAuthSql()
    {
        $sql = <<<'SQL'
  DELETE
  `app2_app_state_v2` FROM `app2_app_state_v2`  
  INNER JOIN `app2_app_instance` ON `app2_app_state_v2`.app_instance_id = `app2_app_instance`.id
  INNER JOIN `app2_app` ON `app2_app`.id = `app2_app_instance`.app_id 
  WHERE `app2_app_state_v2`.name = 'auth' AND `app2_app_state_v2`.entity_id  LIKE 'person:%' 
  AND `app2_app`.name IN ('deskpro-app-trello')
SQL;

        return $sql;
    }

    public function getFixTrelloCardsPermissionsSQL()
    {
        $sql = <<<'SQL'
  UPDATE
  `app2_app_state_v2` 
  INNER JOIN `app2_app_instance` ON `app2_app_state_v2`.app_instance_id = `app2_app_instance`.id
  INNER JOIN `app2_app` ON `app2_app`.id = `app2_app_instance`.app_id
  SET `app2_app_state_v2`.perm_read = 'EVERYBODY', `app2_app_state_v2`.perm_write = 'EVERYBODY'  
  WHERE `app2_app_state_v2`.name = 'cards' AND ( `app2_app_state_v2`.perm_read = 'OWNER' OR `app2_app_state_v2`.perm_write = 'OWNER' )   
  AND `app2_app`.name IN ('deskpro-app-trello')
SQL;

        return $sql;
    }
}
