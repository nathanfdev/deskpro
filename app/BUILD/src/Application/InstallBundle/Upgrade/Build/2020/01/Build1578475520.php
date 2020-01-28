<?php

namespace Application\InstallBundle\Upgrade\Build;

// NOTE: I used the OnlineBuildInterface interface because
//       it looks like your schema changes ARE backwards compatible with the previous version.
//       You should double-check this yourself though. If there are breaking changes, use BlockingBuildInterface instead.

// Please remove these NOTE comments after you have checked the code.

class Build1578475520 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db = $this->getDbConnection('default');

        // Create helpcenter theme sets if they are missing
        $helpcenter = $db->fetchAll('SELECT * FROM theme_sets WHERE theme_id = \'helpcenter\'');
        if (!count($helpcenter)) {
            $brands = $this->getDbConnection('default')->fetchAll('SELECT id, name FROM `brands`');
            foreach ($brands as $brand) {
                $db->insert(
                    'theme_sets',
                    [
                        'brand_id' => $brand['id'],
                        'theme_id' => 'helpcenter',
                    ]
                );
            }
        }
    }
}
