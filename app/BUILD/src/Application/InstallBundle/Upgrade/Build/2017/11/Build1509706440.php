<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1509706440 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE object_aliases (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT DEFAULT NULL, custom_def_ticket_id INT DEFAULT NULL, custom_def_organization_id INT DEFAULT NULL, custom_def_people_id INT DEFAULT NULL, ticket_triggers_id INT DEFAULT NULL, alias VARCHAR(200) NOT NULL, object_type VARCHAR(255) NOT NULL, INDEX IDX_5F5C3B9963B454A1 (app_instance_id), INDEX IDX_5F5C3B999DA6716B (custom_def_ticket_id), INDEX IDX_5F5C3B99E856A96F (custom_def_organization_id), INDEX IDX_5F5C3B99DCE1FF8F (custom_def_people_id), INDEX IDX_5F5C3B99E40E1167 (ticket_triggers_id), UNIQUE INDEX unique_alias (alias, app_instance_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', "CREATE TABLE ticket_webhooks (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT DEFAULT NULL, auth_id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, payload_decoder VARCHAR(255) NOT NULL, is_enabled TINYINT(1) NOT NULL, search_terms LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', terms LONGTEXT DEFAULT NULL COMMENT '(DC2Type:dp_json_obj)', actions LONGTEXT NOT NULL COMMENT '(DC2Type:dp_json_obj)', INDEX IDX_1CE5B35C63B454A1 (app_instance_id), UNIQUE INDEX auth_id_unique (auth_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B9963B454A1 FOREIGN KEY (app_instance_id) REFERENCES app2_app_instance (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B999DA6716B FOREIGN KEY (custom_def_ticket_id) REFERENCES custom_def_ticket (id)');
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B99E856A96F FOREIGN KEY (custom_def_organization_id) REFERENCES custom_def_organizations (id)');
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B99DCE1FF8F FOREIGN KEY (custom_def_people_id) REFERENCES custom_def_people (id)');
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B99E40E1167 FOREIGN KEY (ticket_triggers_id) REFERENCES ticket_triggers (id)');
        $this->execDbQuery('default', 'ALTER TABLE ticket_webhooks ADD CONSTRAINT FK_1CE5B35C63B454A1 FOREIGN KEY (app_instance_id) REFERENCES app2_app_instance (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE app2_app_instance DROP FOREIGN KEY FK_B1B171047987212D');
        $this->execDbQuery('default', "ALTER TABLE app2_app_instance ADD `is_installed` TINYINT(1) DEFAULT '0' NOT NULL");
        $this->execDbQuery('default', 'ALTER TABLE app2_app_instance ADD CONSTRAINT FK_B1B171047987212D FOREIGN KEY (app_id) REFERENCES app2_app (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_asset_blob DROP FOREIGN KEY FK_B4A87CA07987212D');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_asset_blob ADD CONSTRAINT FK_B4A87CA07987212D FOREIGN KEY (app_id) REFERENCES app2_app (id) ON DELETE CASCADE');

        $this->execDbQuery('default', 'DROP INDEX name_unique ON app2_app');
        $this->execDbQuery('default', "ALTER TABLE app2_app ADD `is_dev` TINYINT(1) DEFAULT '0' NOT NULL");
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX name_unique ON app2_app (name, is_dev)');
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE app2_app_instance SET `is_installed` = 1');

        // install trello ...

        $connection     = $this->getDbConnection();
        $instanceIds    = $connection->fetchAllCol("SELECT i.id FROM app2_app_instance i INNER JOIN app2_app a WHERE a.name = 'deskpro-app-trello'");
        $customFieldIds = $this->runCreateTrelloCustomFields($connection, $instanceIds);
        $this->runCopyTrelloTicketStateToCustomFields($connection, $instanceIds, $customFieldIds);
    }

    /**
     * @param \Application\DeskPRO\DBAL\Connection $connection
     * @param array                                $instanceIds
     *
     * @return array
     */
    private function runCreateTrelloCustomFields($connection, $instanceIds)
    {
        $customFieldAlias  = 'trelloCards';
        $customFieldIdList = [];

        foreach ($instanceIds as $id) {
            // create custom def
            $customDefTicket = [
                'js_class'             => '',
                'has_form_template'    => '0',
                'has_display_template' => '0',
                'title'                => 'Trello linked cards',
                'description'          => '',
                'handler_class'        => 'Application\\DeskPRO\\CustomFields\\Handler\\DataList',
                'options'              => 'a:1:{s:20:\"custom_css_classname\";s:0:\"\";}',
                'is_user_enabled'      => '0',
                'is_enabled'           => '1',
                'display_order'        => '0',
                'is_agent_field'       => '0',
            ];
            $connection->insert('custom_def_ticket', $customDefTicket);
            $customDefTicketId = $connection->lastInsertId();

            // create object alias
            $objectAlias = [
                'app_instance_id'      => $id,
                'custom_def_ticket_id' => $customDefTicketId,
                'alias'                => $customFieldAlias,
                'object_type'          => 'custom_def_ticket',
            ];
            $connection->insert('object_aliases', $objectAlias);

            $customFieldIdList[] = $customDefTicketId;
        }

        return $customFieldIdList;
    }

    /**
     * @param \Application\DeskPRO\DBAL\Connection $connection
     * @param array                                $instanceIds  ordered list of instance ids
     * @param array                                $customFields ordered list of custom fields
     */
    private function runCopyTrelloTicketStateToCustomFields($connection, $instanceIds, $customFields)
    {
        reset($customFields);
        foreach ($instanceIds as $id) {
            $customField = current($customFields);
            next($customFields);

            $items = $connection->fetchAll('SELECT id, entity_id, `value` FROM app2_app_state_v2  where app_instance_id = ? and entity_id LIKE ?', [$id, 'ticket:%']);
            foreach ($items as $item) {
                $decoded    = json_decode($item['value'], true);
                $shouldCopy = is_array($decoded)
                    && array_key_exists('trello_cards', $decoded)
                    && is_array($decoded['trello_cards'])
                ;

                if ($shouldCopy) {
                    $ticketId = str_replace('ticket:', '', $item['entity_id']);
                    foreach ($decoded['trello_cards'] as $cardId) {
                        $customDataTicket = [
                            'ticket_id'     => $ticketId,
                            'field_id'      => $customField,
                            'root_field_id' => $customField,
                            'value'         => 0,
                            'input'         => $cardId,
                        ];
                        $connection->insert('custom_data_ticket', $customDataTicket);
                    }
                    $connection->delete('app2_app_state_v2', ['id' => $item['id']]);
                }
            }
        }
    }
}
