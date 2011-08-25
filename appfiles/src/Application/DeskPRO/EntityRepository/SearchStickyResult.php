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

class SearchStickyResult extends EntityRepository
{
	public function getWordsForObject($object)
	{
		if ($object instanceof \Application\DeskPRO\Entity\Article) {
			$object_type = 'DeskPRO:Article';
		} elseif ($object instanceof \Application\DeskPRO\Entity\Download) {
			$object_type = 'DeskPRO:Download';
		} elseif ($object instanceof \Application\DeskPRO\Entity\News) {
			$object_type = 'DeskPRO:News';
		} else {
			throw new \InvalidArgumentException("Unknow type");
		}

		return $this->getWordsFor($object_type, $object->id);
	}

	public function getWordsFor($object_type, $object_id)
	{
		return $this->getEntityManager()->getConnection()->fetchAllCol("
			SELECT word
			FROM search_sticky_result
			WHERE object_type = ? AND object_id = ?
		", array($object_type, $object_id));
	}
}
