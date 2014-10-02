<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Orb\Util\CheckedOptionsArray;

/**
 * Filters based on ticket user name
 *
 * @option string name
 */
class FilterUserName extends AbstractFilterTerm
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('name');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getFilterQuery(ExecutorContextInterface $context = null)
	{
		$options = $this->getTermOptions();
		$check_value = $options['name'];
		$like_value = null;

		switch ($this->getTermOperator()) {
			case self::OP_IS:
			case self::OP_NOT:
				break;
			case self::OP_NOT:
			case self::OP_CONTAINS:
				$like_value = $check_value;
				$like_value = str_replace('%', '%%', $like_value);
				$like_value = str_replace('_', '__', $like_value);
				$like_value = '%' . $like_value . '%';
				break;
			default:
				throw new \InvalidArgumentException("Invalid operator: {$this->getTermOperator()}");
		}

		$query = new FilterQuery();

		foreach (array('name', 'first_name', 'last_name') as $k => $field_name) {
			switch ($this->getTermOperator()) {
				case self::OP_IS:
				case self::OP_NOT:
					$use_op = $this->getTermOptions() == self::OP_NOT ? '!=' : '=';
					$query->orWhere("$field_name $use_op {param.str$k}");
					$query->setParameter('str'.$k, $check_value);
					break;
				case self::OP_NOT:
				case self::OP_CONTAINS:
					$use_op = $this->getTermOptions() == self::OP_NOT ? 'NOT LIKE' : 'LIKE';
					$query->orWhere("$field_name $use_op {param.str$k}");
					$query->setParameter('str'.$k, $like_value);
					break;
			}
		}

		return $query;
	}
}