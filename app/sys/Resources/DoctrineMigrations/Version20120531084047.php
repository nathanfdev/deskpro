<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120531084047 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("CREATE TABLE login_log (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, area VARCHAR(20) NOT NULL, is_success TINYINT(1) NOT NULL, ip_address VARCHAR(20) NOT NULL, hostname VARCHAR(20) NOT NULL, user_agent VARCHAR(20) NOT NULL, date_created DATETIME NOT NULL, INDEX IDX_F16D9FFF217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
        $this->addSql("ALTER TABLE login_log ADD CONSTRAINT FK_F16D9FFF217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
