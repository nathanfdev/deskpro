<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1575944100 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->execDbQuery('default', 'UPDATE custom_def_community_topic SET is_global = 1 WHERE parent_id IS NULL');

        $connection = $this->getDbConnection('default');

        $fieldIds = $connection->fetchAllCol('SELECT id FROM custom_def_community_topic WHERE parent_id IS NULL AND sys_name IS NULL');
        $forumIds = $connection->fetchAllCol('SELECT id FROM community_forums');

        foreach ($fieldIds as $fieldId) {
            foreach ($forumIds as $forumId) {
                $this->execDbQuery('default', "INSERT IGNORE community_forum_to_custom_def_community_topic (forum_id, field_id) VALUES ($forumId, $fieldId)");
            }
        }
    }
}
