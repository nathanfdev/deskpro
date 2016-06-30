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

namespace Application\InstallBundle\Upgrade\Build;

class Build1467269813 extends AbstractBuild
{
    public function run()
    {
        $this->out('Cleanup custom data. Strict data consistency.');

        $customDataTables = [
            'custom_data_organizations' => 'organization_id',
            'custom_data_ticket'        => 'ticket_id',
            'custom_data_article'       => 'article_id',
            'custom_data_person'        => 'person_id',
            'custom_data_billing'       => 'ticket_charge_id',
            'custom_data_chat'          => 'conversation_id',
            'custom_data_feedback'      => 'feedback_id',
            'custom_data_product'       => 'product_id',
        ];

        foreach ($customDataTables as $table => $ownerField) {
            $this->execDbQuery(
                'default',
                "DELETE FROM `$table` WHERE `field_id` IS NULL OR `root_field_id` IS NULL OR `$ownerField` IS NULL"
            );

            $changeSQL = <<<SQL
CHANGE `$ownerField` `$ownerField` INT NOT NULL,
CHANGE `root_field_id` `root_field_id` INT NOT NULL,
CHANGE `field_id` `field_id` INT NOT NULL
SQL;
            $this->execSlowAlterTable($table, $changeSQL);
            $this->execDbQuery('default', "ALTER TABLE $table DROP INDEX `field_id_idx`");
        }
    }
}
