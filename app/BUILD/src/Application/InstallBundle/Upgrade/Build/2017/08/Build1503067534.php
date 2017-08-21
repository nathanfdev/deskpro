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

class Build1503067534 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', "CREATE TABLE app2_app_object_connection (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, custom_def_ticket_id INT DEFAULT NULL, custom_def_organization_id INT DEFAULT NULL, custom_def_people_id INT DEFAULT NULL, ticket_triggers_id INT DEFAULT NULL, alias VARCHAR(200) NOT NULL, object_type VARCHAR(255) NOT NULL, INDEX IDX_1C80DCBB7987212D (app_id), INDEX IDX_1C80DCBB9DA6716B (custom_def_ticket_id), INDEX IDX_1C80DCBBE856A96F (custom_def_organization_id), INDEX IDX_1C80DCBBDCE1FF8F (custom_def_people_id), INDEX IDX_1C80DCBBE40E1167 (ticket_triggers_id), UNIQUE INDEX unique_alias (app_id, alias), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->execDbQuery('default', "ALTER TABLE app2_app_object_connection ADD CONSTRAINT FK_1C80DCBB7987212D FOREIGN KEY (app_id) REFERENCES app2_app (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_object_connection ADD CONSTRAINT FK_1C80DCBB9DA6716B FOREIGN KEY (custom_def_ticket_id) REFERENCES custom_def_ticket (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_object_connection ADD CONSTRAINT FK_1C80DCBBE856A96F FOREIGN KEY (custom_def_organization_id) REFERENCES custom_def_organizations (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_object_connection ADD CONSTRAINT FK_1C80DCBBDCE1FF8F FOREIGN KEY (custom_def_people_id) REFERENCES custom_def_people (id)");
        $this->execDbQuery('default', "ALTER TABLE app2_app_object_connection ADD CONSTRAINT FK_1C80DCBBE40E1167 FOREIGN KEY (ticket_triggers_id) REFERENCES ticket_triggers (id)");
    }


    public function runAlters()
    {
        $sh           = $this->getSchemaHelper();

        if ($idx = $sh->findIndex('custom_data_ticket', ['field_id', 'ticket_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_ticket");
        }

        if ($idx = $sh->findIndex('custom_data_article', ['field_id', 'article_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_article");
        }

        if ($idx = $sh->findIndex('custom_data_billing', ['field_id', 'ticket_charge_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_billing");
        }

        if ($idx = $sh->findIndex('custom_data_chat', ['field_id', 'conversation_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_chat");
        }

        if ($idx = $sh->findIndex('custom_data_feedback', ['field_id', 'feedback_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_feedback");
        }

        if ($idx = $sh->findIndex('custom_data_organizations', ['field_id', 'organization_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_organizations");
        }

        if ($idx = $sh->findIndex('custom_data_person', ['field_id', 'person_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_person");
        }

        if ($idx = $sh->findIndex('custom_data_product', ['field_id', 'product_id', 'root_field_id'])) {
            $this->execDbQuery('default', "DROP INDEX {$idx->getName()} ON custom_data_product");
        }

    }

    public function run()
    {
    }
}
