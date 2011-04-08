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

class News extends EntityRepository
{
	public function getNews($node, $num = 20)
	{
		if ($node) {
			$news = $this->getEntityManager()->createQuery("
				SELECT n
				FROM DeskPRO:News n
				WHERE n.category = ?1
				ORDER BY n.id DESC
			")->setParameter(1, $node)->setMaxResults($num)->execute();
		} else {
			$news = $this->getEntityManager()->createQuery("
				SELECT n
				FROM DeskPRO:News n
				ORDER BY n.id DESC
			")->setMaxResults($num)->execute();
		}

		return $news;
	}
}