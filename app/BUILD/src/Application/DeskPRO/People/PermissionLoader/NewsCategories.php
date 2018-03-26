<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

/**
 * Loads news category permissions.
 *
 * @deprecated use new PermissionsManager to get the PermissionsBag instead of people helpers
 */
class NewsCategories extends BasicCategoryPermission
{
    protected function getCategoryPermissionEntity()
    {
        return 'DeskPRO:NewsCategoryPermission';
    }

    protected function getCategoryEntity()
    {
        return 'DeskPRO:NewsCategory';
    }
}
