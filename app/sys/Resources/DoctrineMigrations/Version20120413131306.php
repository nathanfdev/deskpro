<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120413131306 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE report_dashboard DROP FOREIGN KEY FK_722092E0F675F31B");
        $this->addSql("ALTER TABLE report_dashboard ADD CONSTRAINT FK_722092E0F675F31B FOREIGN KEY (author_id) REFERENCES people (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE report_dashboard_stat DROP FOREIGN KEY FK_67443C5E9502F0B");
        $this->addSql("ALTER TABLE report_dashboard_stat ADD CONSTRAINT FK_67443C5E9502F0B FOREIGN KEY (stat_id) REFERENCES stat (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE stat DROP FOREIGN KEY FK_20B8FF217B185B4D");
        $this->addSql("ALTER TABLE stat DROP FOREIGN KEY FK_20B8FF21F675F31B");
        $this->addSql("ALTER TABLE stat ADD CONSTRAINT FK_20B8FF217B185B4D FOREIGN KEY (parent_stat_id) REFERENCES stat (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE stat ADD CONSTRAINT FK_20B8FF21F675F31B FOREIGN KEY (author_id) REFERENCES people (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE stat_value DROP FOREIGN KEY FK_715085229502F0B");
        $this->addSql("ALTER TABLE stat_value ADD CONSTRAINT FK_715085229502F0B FOREIGN KEY (stat_id) REFERENCES stat (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE stat_value_group DROP FOREIGN KEY FK_4AA43E4370176A35");
        $this->addSql("ALTER TABLE stat_value_group ADD CONSTRAINT FK_4AA43E4370176A35 FOREIGN KEY (stat_value_id) REFERENCES stat_value (id) ON DELETE CASCADE");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
