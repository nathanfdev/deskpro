<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

/**
 * Loads general usergroup permissions likes flags and the like.
 *
 * @deprecated use new PermissionsManager to get the PermissionsBag instead of people helpers
 */
class ArticleCategories extends BasicTreeCategoryPermission
{
    protected function getCategoryPermissionEntity()
    {
        return 'DeskPRO:ArticleCategoryPermission';
    }

    protected function getCategoryEntity()
    {
        return 'DeskPRO:ArticleCategory';
    }
}
