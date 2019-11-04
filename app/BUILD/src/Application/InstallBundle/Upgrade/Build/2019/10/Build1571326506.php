<?php
namespace Application\InstallBundle\Upgrade\Build;

class Build1571326506 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        // Rename tables
        $this->execDbQuery('default', 'RENAME TABLE community_channels TO community_forums');
        $this->execDbQuery('default', 'RENAME TABLE community_channel2usergroup TO community_forum2usergroup');

        // Rename columns
        $this->getSchemaHelper()->renameColumn('community_forum2usergroup', 'community_channel_id', 'community_forum_id');
        $this->getSchemaHelper()->renameColumn('community_topics', 'channel_id', 'forum_id');
    }

    public function run()
    {
        $this->execDbQuery('default', "UPDATE IGNORE `content_search_attribute` SET `attribute_id` = 'forum_id' where `object_type` = 'community'");
    }
}
