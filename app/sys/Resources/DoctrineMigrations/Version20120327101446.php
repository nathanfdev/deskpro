<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120327101446 extends AbstractMigration
{
	public function up(Schema $schema)
	{
		$this->addSql("ALTER TABLE settings DROP id, DROP groupname, DROP default_value, DROP created_at, DROP updated_at, CHANGE name name VARCHAR(255) NOT NULL, CHANGE value value VARBINARY(10000) DEFAULT NULL");
		$this->addSql("ALTER TABLE settings ADD PRIMARY KEY (name)");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
