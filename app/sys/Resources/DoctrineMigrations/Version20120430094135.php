<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120430094135 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE tickets_messages ADD message_full LONGTEXT DEFAULT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
