<?php

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
