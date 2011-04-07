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

class Article extends EntityRepository
{
	public function getArticlesInCategory($category)
	{
		return $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:Article a
			LEFT JOIN a.categories c
			WHERE c = ?1
			ORDER BY a.id DESC
		")->setParameter(1, $category)->execute();
	}
}