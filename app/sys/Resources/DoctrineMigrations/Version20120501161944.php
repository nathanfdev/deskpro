<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120501161944 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE tickets_logs ADD trigger_id INT DEFAULT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
