<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1573497208 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $defaultCategory = null;

        // try to get default category from settings
        $categoryId = $this->container->getSetting('portal.default_community_topic_status_category_id');
        if ($categoryId) {
            $defaultCategory = $this->getDbConnection()->fetchAssoc('SELECT id FROM `community_topic_status_categories` WHERE id = ?', [$categoryId]);
        }

        if (!$defaultCategory) {
            $categoryId = $this->container->getSetting('portal.default_feedback_status_category_id');
            if ($categoryId) {
                $defaultCategory = $this->getDbConnection()->fetchAssoc('SELECT id FROM `community_topic_status_categories` WHERE id = ?', [$categoryId]);
            }
        }

        // if no default category can be found use the first one as fallback
        if (!$defaultCategory) {
            $defaultCategory = $this->getDbConnection()->fetchAssoc("SELECT id FROM `community_topic_status_categories` WHERE status_type = 'active' ORDER BY display_order LIMIT 1");
        }

        if (!$defaultCategory) {
            return;
        }

        // set default old category where it's null
        $this->execDbQueryQuiet('default', "UPDATE `community_topic_status_transitions` st JOIN `community_topics` t ON st.topic_id = t.id SET st.old_status_category_id = {$defaultCategory['id']} WHERE st.old_status_category_id IS NULL AND t.date_updated IS NOT NULL");
    }
}
