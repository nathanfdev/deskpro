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
use Application\DeskPRO\Entity\UsergroupPropertyPermission;

use Orb\Util\Arrays;

/**
 * Loads feedback category permissions
 */
class IdeaCategories extends BasicTreeCategoryPermission
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
