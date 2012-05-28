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

class GroupingField
{
	const DEPARTMENT = 'department';
	const TICKET_CATEGORY = 'ticket_category';
	const TICKET_WORKFLOW = 'ticket_workflow';
	const TICKET_PRIORITY = 'ticket_priority';
	const LANGUAGE = 'language';
	const PRODUCT = 'product';
	const TICKET_FIELD = 'ticket_field';
	const AGENT = 'agent';
	const AGENT_TEAM = 'agent_team';

	/**
	 * @var string
	 */
	protected $field;

	/**
	 * @var int
	 */
	protected $field_id;

	/**
	 * @var null
	 */
	protected $titles = null;

	/**
	 * @param $field
	 * @param null $field_id
	 */
	public function __construct($field, $field_id = null)
	{
		$this->field = $field;
		$this->field_id = $field_id;
	}


	/**
	 * @return null
	 */
	public function getJoin()
	{
		return '';
	}


	public function getFieldName()
	{
		switch ($this->field) {
			case self::DEPARTMENT:
				return 'tickets.department_id';
				break;

			case self::AGENT:
				return 'tickets.agent_id';
				break;

			case self::AGENT_TEAM:
				return 'tickets.agent_team_id';
				break;

			case self::TICKET_CATEGORY:
				return 'tickets.category_id';
				break;

			case self::TICKET_WORKFLOW:
				return 'tickets.workflow_id';
				break;

			case self::TICKET_PRIORITY:
				return 'tickets.priority_id';
				break;

			case self::LANGUAGE:
				return 'tickets.language_id';
				break;

			case self::PRODUCT:
				return 'tickets.product_id';
				break;

			case self::TICKET_FIELD:
				return 'custom_field.id';
				break;

			default:
				throw new \InvalidArgumentException("Invalid field: {$this->field}");
		}
	}


	/**
	 * Get titles
	 *
	 * @return array
	 */
	public function getTitles()
	{
		if ($this->titles !== null) {
			return $this->titles;
		}

		switch ($this->field) {
			case self::DEPARTMENT:
				$this->titles = App::getDataService('Department')->getFullNames();
				break;

			case self::AGENT:
				$this->titles = App::getDataService('Person')->getAgentNames();
				break;

			case self::AGENT_TEAM:
				$this->titles = App::getDataService('AgentTeam')->getTeamNames();
				break;

			case self::TICKET_CATEGORY:
				$this->titles = App::getDataService('TicketCategory')->getFullNames();
				break;

			case self::TICKET_WORKFLOW:
				$this->titles = App::getDataService('TicketWorkflow')->getNames();
				break;

			case self::TICKET_PRIORITY:
				$this->titles = App::getDataService('TicketPriority')->getNames();
				break;

			case self::LANGUAGE:
				$this->titles = App::getDataService('Language')->getTitles();
				break;

			case self::PRODUCT:
				$this->titles = App::getDataService('Product')->getFullNames();
				break;

			case self::TICKET_FIELD:
				$this->titles = array();
				break;

			default:
				throw new \InvalidArgumentException("Invalid field: {$this->field}");
		}

		return $this->titles;
	}
}