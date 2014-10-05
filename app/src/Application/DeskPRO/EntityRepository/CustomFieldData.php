<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Domain\DomainObject;

class CustomFieldData extends AbstractEntityRepository
{
	public function getAllDataAsArrayForOwner(DomainObject $object, DomainObject $context = null)
	{
		$em = $this->getEntityManager();
		$table1 = $em->getClassMetadata($this->getEntityName())->getTableName();
		$table2 = $em->getClassMetadata('DeskPRO:CustomFieldDefinition')->getTableName();

		$qb = $em->getConnection()->createQueryBuilder()
			->select('da.value, da.input, de.id as definition_id, de.parent_id as definition_parent_id')
			->from($table1, 'da')
			->innerJoin('da', $table2, 'de', 'da.definition_id = de.id')
			->where('da.owner_id = :owner_id')
			->andWhere('de.owner_class = :owner_class
				or (de.context_class = :context_class and de.context_id = :cid)')
			->setParameters(array(
				'owner_id' => $object['id'],
				'owner_class' => get_class($object),
			));

		if ($context) {
			$qb
				->andWhere('de.context_class is null or (de.context_class = :context_class and de.context_id = :cid)')
				->setParameter('context_class', get_class($context))
				->setParameter('cid', $context['id']);
		}

		return $qb->execute()->fetchAll();
	}
}
