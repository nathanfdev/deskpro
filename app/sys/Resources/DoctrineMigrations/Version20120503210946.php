<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120503210946 extends AbstractMigration
{
	public function up(Schema $schema)
	{
        $this->addSql("ALTER TABLE labels_articles DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_articles CHANGE article_id article_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_articles (label)");
        $this->addSql("ALTER TABLE labels_articles ADD PRIMARY KEY (article_id, label)");
        $this->addSql("ALTER TABLE labels_blobs DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_blobs CHANGE blob_id blob_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_blobs (label)");
        $this->addSql("ALTER TABLE labels_blobs ADD PRIMARY KEY (blob_id, label)");
        $this->addSql("ALTER TABLE labels_downloads DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_downloads CHANGE download_id download_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_downloads (label)");
        $this->addSql("ALTER TABLE labels_downloads ADD PRIMARY KEY (download_id, label)");
        $this->addSql("ALTER TABLE labels_feedback DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_feedback CHANGE feedback_id feedback_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_feedback (label)");
        $this->addSql("ALTER TABLE labels_feedback ADD PRIMARY KEY (feedback_id, label)");
        $this->addSql("ALTER TABLE labels_news DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_news CHANGE news_id news_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_news (label)");
        $this->addSql("ALTER TABLE labels_news ADD PRIMARY KEY (news_id, label)");
        $this->addSql("ALTER TABLE labels_organizations DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_organizations CHANGE organization_id organization_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_organizations (label)");
        $this->addSql("ALTER TABLE labels_organizations ADD PRIMARY KEY (organization_id, label)");
        $this->addSql("ALTER TABLE labels_people DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_people CHANGE person_id person_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_people (label)");
        $this->addSql("ALTER TABLE labels_people ADD PRIMARY KEY (person_id, label)");
        $this->addSql("ALTER TABLE labels_tasks DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_tasks CHANGE task_id task_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_tasks (label)");
        $this->addSql("ALTER TABLE labels_tasks ADD PRIMARY KEY (task_id, label)");
        $this->addSql("ALTER TABLE labels_tickets DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE labels_tickets CHANGE ticket_id ticket_id INT NOT NULL");
        $this->addSql("CREATE INDEX label_idx ON labels_tickets (label)");
        $this->addSql("ALTER TABLE labels_tickets ADD PRIMARY KEY (ticket_id, label)");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
