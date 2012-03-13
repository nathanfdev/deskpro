<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120313173806 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `tickets_search_active` ADD `creation_system` VARCHAR(20)  NOT NULL  DEFAULT 'web'  AFTER `email_gateway_id`");
        $this->addSql("ALTER TABLE `tickets_search_message` ADD `creation_system` VARCHAR(20)  NOT NULL  DEFAULT 'web'  AFTER `email_gateway_id`");
        $this->addSql("ALTER TABLE `tickets_search_message_active` ADD `creation_system` VARCHAR(20)  NOT NULL  DEFAULT 'web'  AFTER `email_gateway_id`");
    }

    public function down(Schema $schema)
    {
    }
}
