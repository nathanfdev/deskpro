<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120529123048 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("CREATE TABLE page_view_log (id INT AUTO_INCREMENT NOT NULL, object_type INT NOT NULL, object_id INT NOT NULL, person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX object_idx (object_type, object_id), INDEX date_created_idx (date_created), PRIMARY KEY(id)) ENGINE = InnoDB");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
