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

class Build1467654103 extends AbstractBuild
{
    public function run()
    {
        $this->out('Cleanup custom data. Strict data consistency.');

        $customDefsTables = [
            'custom_def_organizations' => [
                'data_table'  => 'custom_data_organizations',
                'owner_field' => 'organization_id',
            ],
            'custom_def_ticket' => [
                'data_table'  => 'custom_data_ticket',
                'owner_field' => 'ticket_id',
            ],
            'custom_def_article' => [
                'data_table'  => 'custom_data_article',
                'owner_field' => 'article_id',
            ],
            'custom_def_people' => [
                'data_table'  => 'custom_data_person',
                'owner_field' => 'person_id',
            ],
            'custom_def_billing' => [
                'data_table'  => 'custom_data_billing',
                'owner_field' => 'ticket_charge_id',
            ],
            'custom_def_chat' => [
                'data_table'  => 'custom_data_chat',
                'owner_field' => 'conversation_id',
            ],
            'custom_def_feedback' => [
                'data_table'  => 'custom_data_feedback',
                'owner_field' => 'feedback_id',
            ],
            'custom_def_products' => [
                'data_table'  => 'custom_data_product',
                'owner_field' => 'product_id',
            ],
        ];

        foreach ($customDefsTables as $defName => $parameters) {
            //lets find mapping field_id => root_field_id (this is for choice type)
            $findSQL = <<<SQL
SELECT * FROM $defName WHERE `parent_id` IS NOT NULL
SQL;
            $choices = $this->getDbConnection('default')->fetchAll($findSQL);
            $map     = [];
            foreach ($choices as $choice) {
                $map[$choice['id']] = $choice['parent_id'];
            }

            $db    = $this->getDbConnection('default');
            $stmnt = $db->prepare("
              UPDATE `{$parameters['data_table']}` 
              SET `root_field_id` = :root_field_id WHERE `field_id` = :field_id AND `root_field_id` IS NULL");
            // try to fix rows where field_id is set, but root_field_id is not - so put propper relation
            foreach ($map as $fieldId => $rootFieldId) {
                $stmnt->execute(['field_id' => $fieldId, 'root_field_id' => $rootFieldId]);
            }
            //now find rows where field_id IS NULL and root_field_id is defined and this is not choice then just set
            //field_id === root_field_id
            $fixSQL = <<<SQL
            UPDATE `{$parameters['data_table']}` as `tab` 
            INNER JOIN `{$defName}` as `def` ON `tab`.`root_field_id` = `def`.`id`
            SET `tab`.`root_field_id` = `def`.`id`
            WHERE `def`.`handler_class` NOT LIKE "%Choice%"
SQL;
            $this->execDbQuery('default', $fixSQL);

            //at last - remove rows where no relation to field_id, root_field_id or ticket_id (e.g.)
            $cleanSQL = <<<SQL
            DELETE FROM `{$parameters['data_table']}` 
  WHERE `field_id` IS NULL OR `root_field_id` IS NULL OR `{$parameters['owner_field']}` IS NULL
SQL;
            $this->execDbQuery('default', $cleanSQL);

            $changeSQL = <<<SQL
CHANGE `{$parameters['owner_field']}` `{$parameters['owner_field']}` INT NOT NULL,
CHANGE `root_field_id` `root_field_id` INT NOT NULL,
CHANGE `field_id` `field_id` INT NOT NULL
SQL;
            // lets find unnecessary index (if exists)
            $index = $this->getSchemaHelper()->findIndex(
                $parameters['data_table'],
                [
                    'field_id',
                    $parameters['owner_field'],
                ]
            );

            if ($index) {
                $changeSQL .= ', DROP INDEX '.$index->getName();
            }

            $this->execSlowAlterTable($parameters['data_table'], $changeSQL);
        }
    }
}
