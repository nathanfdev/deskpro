<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1503398256 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->out('Copy data from old state table');

        // copy app private
        $this->execDbQueryQuiet('default', "
            INSERT INTO app2_app_state_v2 (
                app_instance_id,
                person_id,
                entity_id,
                name,
                value,
                value_type,
                perm_read,
                perm_write,
                is_backend_only,
                persistedAt,
                updatedAt
            ) SELECT 
                app2_app_state.app_instance_id,
                app2_app_state.owner_id,
                CONCAT_WS(':', 'person', app2_app_state.owner_id),
                app2_app_state.name,
                app2_app_state.value,
                'object',
                'OWNER',
                'OWNER',
                0,
                IFNULL(app2_app_state.createdAt, NOW()),
                NOW()
            FROM
                app2_app_state INNER JOIN app2_app_instance ON app2_app_state.app_instance_id =  app2_app_instance.id 
            WHERE app2_app_state.scope = 'private.app'
        ");

        // copy shared app entries, these entries do not have an owner so we assign them to the person with the lowest id
        // which in theory should be the first person added to deskpro which has also used the app
        $this->execDbQueryQuiet('default', "
            INSERT INTO app2_app_state_v2 (
                app_instance_id,
                person_id,
                entity_id,
                name,
                value,
                value_type,
                perm_read,
                perm_write,
                is_backend_only,
                persistedAt,
                updatedAt
            ) SELECT
                app2_app_state.app_instance_id,
                (SELECT MIN(owners.owner_id) FROM app2_app_state owners  WHERE owners.owner_id IS NOT NULL) as owners_id ,
                CONCAT_WS(':', 'ticket', app2_app_state.name),
                'cards',
                app2_app_state.value,
                'object',
                'EVERYBODY',
                'EVERYBODY',
                0,
                IFNULL(app2_app_state.createdAt, NOW()),
                NOW()
            FROM
                app2_app_state INNER JOIN app2_app_instance ON app2_app_state.app_instance_id =  app2_app_instance.id 
            WHERE app2_app_state.scope = 'shared.app'
        ");
    }
}
