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

use Application\DeskPRO\App;
use Doctrine\ORM\EntityRepository;

use Orb\Util\Arrays;

class ArticlePendingCreate extends EntityRepository
{
	public function getPendingArticles()
	{
		$pending_articles = $this->getEntityManager()->createQuery("
			SELECT a, t, p
			FROM DeskPRO:ArticlePendingCreate a
			LEFT JOIN a.ticket t
			LEFT JOIN a.person p
			ORDER BY a.date_created DESC
		")->execute();

		return $pending_articles;
	}


	public function getByIds(array $ids)
	{
		$ids = Arrays::castToType($ids, 'int');
		$ids = Arrays::removeFalsey($ids);

		if (!$ids) {
			return array();
		}
		$ids = implode(',', $ids);

		return $this->getEntityManager()->createQuery("
			SELECT a
			FROM DeskPRO:ArticlePendingCreate a INDEX BY a.id
			WHERE a.id IN ($ids)
		")->execute();
	}
}
