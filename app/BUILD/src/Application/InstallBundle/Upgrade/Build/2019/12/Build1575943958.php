<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1575943958 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_custom_def_community_topic ADD id INT NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_custom_def_community_topic DROP PRIMARY KEY');
        $this->execDbQuery('default', 'ALTER TABLE community_forum_to_custom_def_community_topic CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD PRIMARY KEY (id)');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX unique_key_idx ON community_forum_to_custom_def_community_topic (forum_id, field_id)');
    }

    public function run()
    {
    }
}
