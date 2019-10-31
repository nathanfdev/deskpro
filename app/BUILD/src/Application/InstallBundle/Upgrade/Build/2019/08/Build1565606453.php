<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606453 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE direct_message_threads (id INT AUTO_INCREMENT NOT NULL, participant_ids VARCHAR(255) DEFAULT NULL, date_last_message DATETIME DEFAULT NULL, date_created DATETIME NOT NULL, UNIQUE INDEX UNIQ_4BC51CF17FF87BFB (participant_ids), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE direct_message_participants (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, thread_id INT NOT NULL, is_unread TINYINT(1) NOT NULL, INDEX IDX_E0F1F94B217BBB47 (person_id), INDEX IDX_E0F1F94BE2904019 (thread_id), UNIQUE INDEX dm_person_thread_id (person_id, thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'CREATE TABLE direct_messages (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, message_html LONGTEXT NOT NULL, message_doc LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', date_created DATETIME NOT NULL, INDEX IDX_721C1B5AF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE direct_message_participants ADD CONSTRAINT FK_E0F1F94B217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE direct_message_participants ADD CONSTRAINT FK_E0F1F94BE2904019 FOREIGN KEY (thread_id) REFERENCES direct_message_threads (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE direct_messages ADD CONSTRAINT FK_721C1B5AF675F31B FOREIGN KEY (author_id) REFERENCES direct_message_participants (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
