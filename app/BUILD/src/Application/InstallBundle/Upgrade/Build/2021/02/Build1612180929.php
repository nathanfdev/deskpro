<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1612180929 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQueryQuiet('default', 'CREATE TABLE article_comment_attachments (id BIGINT AUTO_INCREMENT NOT NULL, article_comment_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, person_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_8A52D85AC4B0AC92 (article_comment_id), INDEX IDX_8A52D85AED3E8EA5 (blob_id), INDEX IDX_8A52D85A217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', 'CREATE TABLE communitytopic_comment_attachments (id BIGINT AUTO_INCREMENT NOT NULL, article_comment_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, person_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_52C34BC7C4B0AC92 (article_comment_id), INDEX IDX_52C34BC7ED3E8EA5 (blob_id), INDEX IDX_52C34BC7217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', 'CREATE TABLE news_comment_attachments (id BIGINT AUTO_INCREMENT NOT NULL, news_comment_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, person_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_57564A241416AA6A (news_comment_id), INDEX IDX_57564A24ED3E8EA5 (blob_id), INDEX IDX_57564A24217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', 'CREATE TABLE topic_comment_attachments (id BIGINT AUTO_INCREMENT NOT NULL, topic_comment_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, person_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_8728BA3A3D3AD339 (topic_comment_id), INDEX IDX_8728BA3AED3E8EA5 (blob_id), INDEX IDX_8728BA3A217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', 'CREATE TABLE download_comment_attachments (id BIGINT AUTO_INCREMENT NOT NULL, download_comment_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, person_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_9FB18F87C1AB446E (download_comment_id), INDEX IDX_9FB18F87ED3E8EA5 (blob_id), INDEX IDX_9FB18F87217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQueryQuiet('default', 'ALTER TABLE article_comment_attachments ADD CONSTRAINT FK_8A52D85AC4B0AC92 FOREIGN KEY (article_comment_id) REFERENCES article_comments (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE article_comment_attachments ADD CONSTRAINT FK_8A52D85AED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE article_comment_attachments ADD CONSTRAINT FK_8A52D85A217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE communitytopic_comment_attachments ADD CONSTRAINT FK_52C34BC7C4B0AC92 FOREIGN KEY (article_comment_id) REFERENCES community_topic_comments (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE communitytopic_comment_attachments ADD CONSTRAINT FK_52C34BC7ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE communitytopic_comment_attachments ADD CONSTRAINT FK_52C34BC7217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE news_comment_attachments ADD CONSTRAINT FK_57564A241416AA6A FOREIGN KEY (news_comment_id) REFERENCES news_comments (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE news_comment_attachments ADD CONSTRAINT FK_57564A24ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE news_comment_attachments ADD CONSTRAINT FK_57564A24217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE topic_comment_attachments ADD CONSTRAINT FK_8728BA3A3D3AD339 FOREIGN KEY (topic_comment_id) REFERENCES topic_comments (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE topic_comment_attachments ADD CONSTRAINT FK_8728BA3AED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE topic_comment_attachments ADD CONSTRAINT FK_8728BA3A217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE download_comment_attachments ADD CONSTRAINT FK_9FB18F87C1AB446E FOREIGN KEY (download_comment_id) REFERENCES download_comments (id) ON DELETE SET NULL');
        $this->execDbQueryQuiet('default', 'ALTER TABLE download_comment_attachments ADD CONSTRAINT FK_9FB18F87ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
        $this->execDbQueryQuiet('default', 'ALTER TABLE download_comment_attachments ADD CONSTRAINT FK_9FB18F87217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
