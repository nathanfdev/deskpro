<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120326163019 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE article_comments ADD website VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE download_comments ADD website VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE feedback_comments ADD website VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE news_comments ADD website VARCHAR(255) DEFAULT NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
