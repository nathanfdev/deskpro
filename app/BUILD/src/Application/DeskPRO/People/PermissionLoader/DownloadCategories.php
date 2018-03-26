<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

/**
 * Loads download category permissions.
 *
 * @deprecated use new PermissionsManager to get the PermissionsBag instead of people helpers
 */
class DownloadCategories extends BasicTreeCategoryPermission
{
    protected function getCategoryPermissionEntity()
    {
        return 'DeskPRO:DownloadCategoryPermission';
    }

    protected function getCategoryEntity()
    {
        return 'DeskPRO:DownloadCategory';
    }
}
