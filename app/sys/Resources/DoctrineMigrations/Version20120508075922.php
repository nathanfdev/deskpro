<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120508075922 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("CREATE TABLE feedback_attachments (id INT AUTO_INCREMENT NOT NULL, feedback_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_CC264F12D249A887 (feedback_id), INDEX IDX_CC264F12217BBB47 (person_id), INDEX IDX_CC264F12ED3E8EA5 (blob_id), PRIMARY KEY(id)) ENGINE = InnoDB");
        $this->addSql("ALTER TABLE feedback_attachments ADD CONSTRAINT FK_CC264F12D249A887 FOREIGN KEY (feedback_id) REFERENCES feedback (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE feedback_attachments ADD CONSTRAINT FK_CC264F12217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE feedback_attachments ADD CONSTRAINT FK_CC264F12ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
