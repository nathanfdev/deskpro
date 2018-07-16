<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1531319032 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE news_attachments (id INT AUTO_INCREMENT NOT NULL, news_id INT DEFAULT NULL, person_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME DEFAULT NULL, INDEX IDX_6EF14C99B5A459A0 (news_id), INDEX IDX_6EF14C99217BBB47 (person_id), INDEX IDX_6EF14C99ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE news_attachments ADD CONSTRAINT FK_6EF14C99B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE news_attachments ADD CONSTRAINT FK_6EF14C99217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE news_attachments ADD CONSTRAINT FK_6EF14C99ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
