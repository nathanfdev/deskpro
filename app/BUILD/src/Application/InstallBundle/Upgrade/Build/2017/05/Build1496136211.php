<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
