<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

use \Orb\Util\Arrays;

class ArticleCategoryPermission extends EntityRepository
{
	public function getCategoriesForUsergroups(array $usergroup_ids)
	{
		$usergroup_ids = array_filter($usergroup_ids, function ($val) {
			return ctype_digit($val);
		});
		
		if (!$usergroup_ids) {
			return array();
		}

		$cat_ids = App::getDb()->fetchAllCol("
			SELECT category_id
			FROM article_category_permissions
			WHERE usergroup_id IN (" . implode(',', $usergroup_ids) . ")
		");

		return $cat_ids;
	}
}