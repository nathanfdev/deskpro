<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1505121163 extends AbstractBuild implements BlockingBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE snippet_use_log (id INT AUTO_INCREMENT NOT NULL, date_created DATETIME NOT NULL, ticket_message_id INT DEFAULT NULL, chat_message_id INT DEFAULT NULL, snippet_id INT DEFAULT NULL, language_id INT DEFAULT NULL, person_id INT DEFAULT NULL, rating INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, INDEX date_created (date_created), INDEX IDX_43190050C5E9817D (ticket_message_id), INDEX IDX_43190050948B568F (chat_message_id), INDEX IDX_431900506E34B975 (snippet_id), INDEX IDX_4319005082F1BAF4 (language_id), INDEX IDX_43190050217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_43190050C5E9817D FOREIGN KEY (ticket_message_id) REFERENCES tickets_messages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_43190050948B568F FOREIGN KEY (chat_message_id) REFERENCES chat_messages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_431900506E34B975 FOREIGN KEY (snippet_id) REFERENCES snippets (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_4319005082F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippet_use_log ADD CONSTRAINT FK_43190050217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE snippets ADD `usage_count` INT NOT NULL, ADD positive_ratings INT NOT NULL, ADD neutral_ratings INT NOT NULL, ADD negative_ratings INT NOT NULL');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
