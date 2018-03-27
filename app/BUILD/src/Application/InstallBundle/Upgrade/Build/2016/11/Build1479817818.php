<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817818 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade portal.widget.enabled');

        $connection = $this->getDbConnection('default');

        $brands = $connection->fetchAllCol('select `id` from `brands`');

        $sql = 'insert ignore into `settings_brand` (`brand_id`, `name`, `value`) values (:brand_id, "portal.widget.enabled", 1)';

        $stmnt = $connection->prepare($sql);
        foreach ($brands as $brand) {
            $stmnt->execute(['brand_id' => $brand]);
        }
    }
}
