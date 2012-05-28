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
 * @subpackage
 */

namespace Application\ReportBundle\OverviewStat;

use Application\DeskPRO\App;

class TicketsAwaitingAgent extends AbstractTableOverviewStat
{
	/**
	 * @var GroupingField
	 */
	protected $grouping_field;

	/**
	 * @var int[]
	 */
	protected $values = null;

	/**
	 * @var array
	 */
	protected $titles = null;

	public function __construct(GroupingField $grouping_field)
	{
		$this->grouping_field = $grouping_field;
	}


	/**
	 * @return string[]
	 */
	public function getTitles()
	{
		return $this->grouping_field->getTitles();
	}


	/**
	 * @return int[]
	 */
	public function getValues()
	{
		if ($this->values !== null) {
			return $this->values;
		}

		$group_field = $this->grouping_field->getFieldName();
		$join = $this->grouping_field->getJoin();

		$sql = "
			SELECT $group_field, COUNT(*)
			FROM tickets_search_active AS tickets
			$join
			WHERE tickets.status = 'awaiting_agent'
			GROUP BY $group_field
		";

		$this->values = App::getDb()->fetchAllKeyValue($sql);

		return $this->values;
	}


	/**
	 * @return int
	 */
	public function getMax()
	{
		$max = max($this->getValues());

		if ($max < 10) {
			$max = 10;
		}

		return $max;
	}
}