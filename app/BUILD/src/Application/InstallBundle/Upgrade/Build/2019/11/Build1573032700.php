<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1573032700 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE direct_message_blocks (id INT AUTO_INCREMENT NOT NULL, during_thread_id INT DEFAULT NULL, person_id INT DEFAULT NULL, by_person_id INT DEFAULT NULL, date_created DATETIME NOT NULL, INDEX IDX_316F1F8B427DFCF3 (during_thread_id), INDEX IDX_316F1F8B217BBB47 (person_id), INDEX IDX_316F1F8BB5BE2AA2 (by_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE direct_message_blocks ADD CONSTRAINT FK_316F1F8B427DFCF3 FOREIGN KEY (during_thread_id) REFERENCES direct_message_threads (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE direct_message_blocks ADD CONSTRAINT FK_316F1F8B217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'ALTER TABLE direct_message_blocks ADD CONSTRAINT FK_316F1F8BB5BE2AA2 FOREIGN KEY (by_person_id) REFERENCES people (id) ON DELETE SET NULL');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
