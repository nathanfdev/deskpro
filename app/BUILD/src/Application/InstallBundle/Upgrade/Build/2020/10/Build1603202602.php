<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1603202602 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE guides ADD use_volumes TINYINT(1) NOT NULL');
    }

    public function run()
    {
        $db = $this->getDbConnection('default');

        $guides = $db->fetchAll('SELECT COUNT(t.id) as chapters, g.id FROM guides g LEFT JOIN topics t ON g.id = t.guide_id AND t.status = \'published\' AND t.no_content = 1 AND t.parent_id IS NOT NULL GROUP BY g.id');
        foreach ($guides as $guide) {
            if ($guide['chapters'] > 0) {
                $db->update('guides', [
                    'use_volumes' => '1',
                ], ['id' => $guide['id']]);
            }
        }
    }
}
