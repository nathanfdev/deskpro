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

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\TicketFilter;

class TicketFilterCollection
{
	/**
	 * @var TicketFilter[]
	 */
	private $filters;

	/**
	 * @var array
	 */
	private $caches = array();

	/**
	 * @param TicketFilter[] $filters
	 */
	public function __construct(array $filters)
	{
		$this->filters = array_values($filters);
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketFilter[]
	 */
	public function getAllFilters()
	{
		return $this->filters;
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketFilter[]
	 */
	public function getSystemFilters()
	{
		if (isset($this->cached['getSystemFilters'])) {
			return $this->cached['getSystemFilters'];
		}

		$this->cached['getSystemFilters'] = array_filter($this->filters, function($f) {
			return $f->sys_name !== null && strpos($f->sys_name, 'w_hold') === false;
		});

		return $this->cached['getSystemFilters'];
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketFilter[]
	 */
	public function getSystemHoldFilters()
	{
		if (isset($this->cached['getSystemHoldFilters'])) {
			return $this->cached['getSystemHoldFilters'];
		}

		$this->cached['getSystemHoldFilters'] = array_filter($this->filters, function($f) {
			return $f->sys_name !== null && strpos($f->sys_name, 'w_hold') !== false;
		});

		return $this->cached['getSystemHoldFilters'];
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketFilter[]
	 */
	public function getCustomFilters()
	{
		if (isset($this->cached['getCustomFilters'])) {
			return $this->cached['getCustomFilters'];
		}

		$this->cached['getCustomFilters'] = array_filter($this->filters, function($f) {
			return $f->sys_name === null;
		});

		return $this->cached['getCustomFilters'];
	}
}