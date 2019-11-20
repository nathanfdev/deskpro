<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1573737940 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD official_response_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topics ADD CONSTRAINT FK_E03CB3CA2E3F174A FOREIGN KEY (official_response_id) REFERENCES community_topic_comments (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_E03CB3CA2E3F174A ON community_topics (official_response_id)');
    }

    public function run()
    {
    }
}
