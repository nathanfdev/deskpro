<?php

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0024_custom_data_unique extends AbstractBuild
{
    public function run()
    {
        $this->execDbQueryQuiet('default', 'ALTER TABLE `custom_data_article`
            ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `article_id` ASC, `root_field_id` ASC);');

        $this->execDbQueryQuiet('default', 'ALTER TABLE `custom_data_billing`
            ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `ticket_charge_id` ASC, `root_field_id` ASC);');

        $this->execDbQueryQuiet('default', 'ALTER TABLE `custom_data_chat`
            ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `conversation_id` ASC, `root_field_id` ASC);');

        $this->execDbQueryQuiet('default', 'ALTER TABLE `custom_data_feedback`
            ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `feedback_id` ASC, `root_field_id` ASC);');

        $this->execDbQueryQuiet('default', 'ALTER TABLE `custom_data_organizations`
            ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `organization_id` ASC, `root_field_id` ASC);');

        $this->execDbQueryQuiet('default', 'ALTER TABLE `custom_data_product`
            ADD UNIQUE INDEX `unique_idx` (`field_id` ASC, `product_id` ASC, `root_field_id` ASC);');
    }
}

//[[build:1460678411]]
