<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120502134414 extends AbstractMigration
{
	public function up(Schema $schema)
	{
		$this->addSql("UPDATE email_sources SET source_info = NULL");
		$this->addSql("ALTER TABLE email_sources CHANGE source_info source_info LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)'");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
