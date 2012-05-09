<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120509192245 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE custom_def_article ADD description LONGTEXT NOT NULL");
        $this->addSql("ALTER TABLE custom_def_feedback ADD description LONGTEXT NOT NULL");
        $this->addSql("ALTER TABLE custom_def_organizations ADD description LONGTEXT NOT NULL");
        $this->addSql("ALTER TABLE custom_def_people ADD description LONGTEXT NOT NULL");
        $this->addSql("ALTER TABLE custom_def_ticket ADD description LONGTEXT NOT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
