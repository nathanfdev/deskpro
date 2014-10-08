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
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\DBAL\Connection;

class CustomFieldDefinition extends AbstractEntityRepository
{
	public function getAllDefinitionsForOwner(DomainObject $object, DomainObject $context = null, Layout $layout = null)
	{
		$qb = $this->createQueryBuilder('d')
			->where('d.parent is null')
			->andWhere('d.owner_class = :owner')
			->andWhere('d.is_enabled = 1')
			->setParameter('owner', get_class($object));

		if ($layout) {
			if (!$in = $layout->getIdsOfFieldType('custom_field')) {
				return array();
			}
			$qb
				->andWhere('d.id in (:fields)')
				->setParameter('fields', $in, Connection::PARAM_INT_ARRAY);
		}

		if ($context) {
			$qb
				->leftJoin('d.children', 'dc')
				->andWhere('d.context_class is null
					or (d.context_class = :context_class and (d.context_id = :cid or dc.context_id = :cid))')
				->setParameter('context_class', get_class($context))
				->setParameter('cid', $context['id']);
		}

		return $qb->getQuery()->getResult();
	}
}
