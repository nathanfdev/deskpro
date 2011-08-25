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
use Application\DeskPRO\ORM\QueryPartial;

use Doctrine\ORM\EntityRepository;

class CommentAbstract extends EntityRepository
{
	const FIELD = '';

	public function getByIds(array $ids)
	{
		if (!$ids) return array();

		$ids = implode(',', $ids);

		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM " . $this->_entityName ." c INDEX BY c.id
			WHERE c.id IN ($ids)
		")->execute();
	}

	public function getComments($object, $show_validating = true)
	{
		if ($show_validating) {
			return $this->getEntityManager()->createQuery("
				SELECT c
				FROM " . $this->_entityName ." c
				WHERE c.status != ?1 AND c." . static::FIELD . " = ?2
				ORDER BY c.id DESC
			")->setParameter(1, 'deleted')->setParameter(2, $object)->execute();
		} else {
			return $this->getEntityManager()->createQuery("
				SELECT c
				FROM " . $this->_entityName ." c
				WHERE c.status = ?1 AND c." . static::FIELD . " = ?2
				ORDER BY c.id DESC
			")->setParameter(1, 'visible')->setParameter(2, $object)->execute();
		}
	}

	public function countAwaitingValidation()
	{
		$table = $this->getClassMetadata()->getTableName();
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM $table
			WHERE status = ?
		", array('validating'));
	}

	public function getValidatingComments()
	{
		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM " . $this->_entityName ." c
			LEFT JOIN c.person p
			WHERE c.status = ?1
			ORDER BY c.id DESC
		")->setParameter(1, 'validating')->execute();
	}
}
