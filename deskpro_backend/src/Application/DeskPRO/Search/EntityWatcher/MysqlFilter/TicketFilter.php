<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\EntityWatcher\MysqlFilter;

use Doctrine\ORM\EntityManager;
use Orb\Filter\FilterInterface;

class TicketFilter implements FilterInterface
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function filter($ticket)
	{
		// Means its new, always index
		if (!$ticket->id) {
			return true;
		}

		$uow = $this->em->getUnitOfWork();
		$changeset = $uow->getEntityChangeSet();

		$valid_triggers = array(
			'language', 'department', 'category', 'priority', 'workflow', 'product',
			'person', 'agent', 'agent_team', 'organization', 'messages', 'labels',
			'status', 'is_hold', 'subject',
		);

		foreach ($valid_triggers as $k) {
			if (isset($changeset[$k])) {
				return true;
			}
		}

		return false;
	}
}
