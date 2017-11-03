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

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Guide;

/**
 * Loads download category permissions.
 *
 * @deprecated use new PermissionsManager to get the PermissionsBag instead of people helpers
 */
class Guides extends BasicTreeCategoryPermission
{
    protected function getCategoryPermissionEntity()
    {
        return 'DeskPRO:GuidePermission';
    }

    protected function getCategoryEntity()
    {
        return 'DeskPRO:Guide';
    }

    protected function init()
    {
        $this->specific_cats = App::getEntityRepository(Guide::class)->getGuidesForUsergroups($this->getUsergroupIds());

        $this->_computeTree(null);

        $allIds                = App::getEntityRepository(Guide::class)->getIds();
        $this->disallowed_cats = array_diff($allIds, $this->allowed_cats);
    }

    protected function _computeTree($tree, $default = null)
    {
        $this->allowed_cats = $this->specific_cats;
    }
}
