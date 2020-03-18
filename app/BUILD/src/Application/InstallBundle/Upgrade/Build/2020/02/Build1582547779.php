<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1582547779 extends AbstractBuild implements OnlineBuildInterface
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
