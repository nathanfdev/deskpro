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
use \Orb\Util\Strings;

class Idea extends EntityRepository
{
	public function getBySlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
		if (!$id) return null;

		return $this->find($id);
	}
	
	public function getIdeas($status, $node = false, $sort = 'id', $num = 10)
	{
		if ($sort == 'date') $sort = 'id';
		if (!in_array($sort, array('id', 'num_votes'))) $sort = 'id';

		if ($node) {
			$node_ids = $node->getTreeIds(true);

			$ideas = $this->getEntityManager()->createQuery("
				SELECT i
				FROM DeskPRO:Idea i
				WHERE i.category IN (".implode(',', $node_ids).") AND i.status = ?1
				ORDER BY i.$sort DESC
			")->setParameter(1, $status)->setMaxResults($num)->execute();
		} else {
			$ideas = $this->getEntityManager()->createQuery("
				SELECT i
				FROM DeskPRO:Idea i
				WHERE i.status = ?1
				ORDER BY i.$sort DESC
			")->setParameter(1, $status)->setMaxResults($num)->execute();
		}

		return $ideas;
	}
}