<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

/**
 * Loads feedback category permissions.
 *
 * @deprecated use new PermissionsManager to get the PermissionsBag instead of people helpers
 */
class FeedbackCategories extends BasicTreeCategoryPermission
{
    protected function getCategoryPermissionEntity()
    {
        return 'DeskPRO:FeedbackCategoryPermission';
    }

    protected function getCategoryEntity()
    {
        return 'DeskPRO:FeedbackCategory';
    }
}
