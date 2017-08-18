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
