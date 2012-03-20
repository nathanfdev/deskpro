<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120320141525 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE templates ADD date_created DATETIME NOT NULL, ADD date_updated DATETIME NOT NULL, DROP created_at, DROP updated_at, CHANGE path name VARCHAR(255) NOT NULL, CHANGE template template_code LONGTEXT NOT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
