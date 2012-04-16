<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120416104121 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE tickets ADD notify_email_name VARCHAR(200) NOT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
