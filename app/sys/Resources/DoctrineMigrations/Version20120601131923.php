<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120601131923 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE sessions ADD is_chat_available TINYINT(1) NOT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
