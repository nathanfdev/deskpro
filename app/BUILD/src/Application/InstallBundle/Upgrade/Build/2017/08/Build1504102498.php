<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\InstallBundle\Upgrade\Build;

// NOTE: I used the BlockingBuildInterface interface because
//       it looks like your schema changes are NOT backwards compatible with the previous version.
//       You should double-check this yourself though. If they are backwards compatible, use OnlineBuildInterface instead.

// Please remove these NOTE comments after you have checked the code.

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

        $updateManifestSql = <<<'SQL'
  UPDATE
  `app2_app`
  SET `manifest` = ?
  WHERE `app2_app`.id =  ?;
SQL;
        $fixedManifests = $this->fixTrelloCardsPermissionsInManifest();
        foreach ($fixedManifests as $key => $value) {
            $this->getDbConnection('default')->executeQuery($updateManifestSql, [$value, $key]);
        }
    }

    public function getFixTrelloAuthSql()
    {
        $sql = <<<'SQL'
  UPDATE
  `app2_app_state_v2` 
  INNER JOIN `app2_app_instance` ON `app2_app_state_v2`.app_instance_id = `app2_app_instance`.id
  INNER JOIN `app2_app` ON `app2_app`.id = `app2_app_instance`.app_id
  SET `app2_app_state_v2`.entity_id = CONCAT_WS(':', 'app', `app2_app`.id) 
  WHERE `app2_app_state_v2`.name = 'auth' AND `app2_app_state_v2`.entity_id  LIKE 'person:%' 
  AND `app2_app`.name IN ('deskpro-app-trello');
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
  AND `app2_app`.name IN ('deskpro-app-trello');
SQL;
        return $sql;
    }

    public function fixTrelloCardsPermissionsInManifest()
    {
        $sql = <<<'SQL'
SELECT
  `id`, `manifest` from 
  `app2_app`    
  WHERE `app2_app`.name IN ('deskpro-app-trello');
SQL;

        $fixedManifests = [];

        $entries = $this->getDbConnection()->fetchAll($sql);
        foreach ($entries as $trelloApp) {
            $fixedManifest = $this->fixManifest($trelloApp['manifest']);
            if ($fixedManifest) {
                $id = $trelloApp['id'];
                $fixedManifests[$id] = $fixedManifest;
            }
        }

        return $fixedManifests;
    }

    private function fixManifest($manifest) {
        $parsed = json_decode($manifest, $assoc = true);
        if (! is_array($parsed)) {
            return null;
        }

        $wasFixed = false;
        if (array_key_exists('state', $parsed) && is_array($parsed['state'])) {
            foreach ($parsed['state'] as $key => $state) {
                if (
                    is_array($state)
                    && array_key_exists('name', $state)
                    && is_string($state['name'])
                    && array_key_exists('permRead', $state)
                    && is_string($state['permRead'])
                    && array_key_exists('permWrite', $state)
                    && is_string($state['permWrite'])
                    && $state['name'] === 'cards'
                    && ( $state['permRead'] !== 'EVERYBODY' || $state['permWrite'] !== 'EVERYBODY' )
                ) {
                    $parsed['state'][$key]['permRead'] = 'EVERYBODY';
                    $parsed['state'][$key]['permWrite'] = 'EVERYBODY';
                    $wasFixed = true;
                }
            }
        }

        return $wasFixed ? json_encode($parsed) : null;
    }


}
