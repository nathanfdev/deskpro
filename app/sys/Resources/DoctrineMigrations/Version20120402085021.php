<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120402085021 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE `tickets_search_active` ADD `date_resolved` DATETIME  DEFAULT NULL  AFTER `total_to_first_reply`");
        $this->addSql("ALTER TABLE `tickets_search_message` ADD `date_resolved` DATETIME  DEFAULT NULL  AFTER `content`");
        $this->addSql("ALTER TABLE `tickets_search_message_active` ADD `date_resolved` DATETIME  DEFAULT NULL  AFTER `content`");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
