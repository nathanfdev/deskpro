<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;

/**
 * Filters based on ticket user disabled status
  */
class FilterUserIsDisabled extends AbstractFilterTerm
{
	/**
	 * {@inheritDoc}
	 */
	public function getFilterQuery(ExecutorContextInterface $context = null)
	{
		$query = new FilterQuery();
		switch ($this->getTermOperator()) {
			case self::OP_IS:
				$query->andWhere('user.is_disabled = 1');
			case self::OP_NOT:
				$query->andWhere('user.is_disabled = 0');
				break;
			default:
				throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
		}

		$query->addJoin('tickets.person', 'people', 'user', 'user.person_id = tickets.person_id');
		return $query;
	}
}