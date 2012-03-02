<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;

use Orb\Util\Arrays;

/**
 * Loads general usergroup permissions likes flags and the like.
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
