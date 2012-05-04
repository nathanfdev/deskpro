<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120504091947 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("CREATE TABLE chat_conversation_pings (id INT AUTO_INCREMENT NOT NULL, chat_id INT NOT NULL, ping_time INT NOT NULL, INDEX chat_id_idx (chat_id), PRIMARY KEY(id)) ENGINE = InnoDB");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
