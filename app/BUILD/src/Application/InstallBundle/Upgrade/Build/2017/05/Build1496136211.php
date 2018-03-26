<?php

namespace Application\InstallBundle\Upgrade\Build;

use DeskPRO\Component\Util\ListUtils;

class Build1496136211 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $db       = $this->getDbConnection();
        $brandIds = $db->fetchAllCol('SELECT id FROM brands');

        if (count($brandIds) === 1) {
            $depIds            = $db->fetchAllCol('SELECT id FROM departments');
            $depsWithBrands    = $db->fetchAllCol('SELECT department_id FROM department_to_brand');
            $depsWithoutBrands = array_diff($depIds, $depsWithBrands);

            $brandId = $brandIds[0];

            if ($depsWithoutBrands) {
                $inserts = ListUtils::map($depsWithoutBrands, function ($depId) use ($brandId) {
                    return ['brand_id' => $brandId, 'department_id' => $depId];
                });

                $db->batchInsert('department_to_brand', $inserts);
                $this->out(sprintf('Fixed %d departments with missing brand assoc', count($depsWithoutBrands)));
            }
        }
    }
}
