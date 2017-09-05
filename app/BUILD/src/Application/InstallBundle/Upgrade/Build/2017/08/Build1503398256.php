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
