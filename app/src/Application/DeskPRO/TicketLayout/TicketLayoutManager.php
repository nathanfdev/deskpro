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
 * @category TicketLayout
 */

namespace Application\DeskPRO\TicketLayout;

class TicketLayoutManager
{
	/**
	 * Layouts keyed by department ID
	 * @var LayoutCollection
	 */
	private $user_layouts;

	/**
	 * Layouts keyed by deartmend ID
	 * @var LayoutCollection
	 */
	private $agent_layouts;


	/**
	 * @param \Application\DeskPRO\Entity\TicketLayout[] $ticket_layouts
	 * @return TicketLayoutManager
	 */
	public static function createWithLayoutRecords(array $ticket_layouts)
	{
		$user_layouts  = new LayoutCollection();
		$agent_layouts = new LayoutCollection();

		foreach ($ticket_layouts as $l) {
			$key = $l->department ? $l->department->getId() : null;
			if ($user_layout = $l->user_layout) {
				LayoutUtil::ensureMinimumUserLayout($user_layout);
				$user_layouts->addLayout($user_layout, $key);
			}
			if ($agent_layout = $l->agent_layout) {
				LayoutUtil::ensureMinimumAgentLayout($agent_layout);
				$agent_layouts->addLayout($agent_layout, $key);
			}
		}

		return new self($user_layouts, $agent_layouts);
	}


	/**
	 * @param array $ticket_layouts
	 * @return TicketLayoutManager
	 */
	public static function createWithLayoutArrays(array $ticket_layouts)
	{
		$user_layouts  = new LayoutCollection();
		$agent_layouts = new LayoutCollection();

		foreach ($ticket_layouts as $l) {
			$key = $l['department_id'] ? $l['department_id'] : null;
			if (!empty($l['user_layout'])) {
				$user_layout = $l['user_layout'];
				LayoutUtil::ensureMinimumUserLayout($user_layout);
				$user_layouts->addLayout($user_layout, $key);
			}
			if (!empty($l['agent_layout'])) {
				$agent_layout = $l['agent_layout'];
				LayoutUtil::ensureMinimumAgentLayout($agent_layout);
				$agent_layouts->addLayout($agent_layout, $key);
			}
		}

		return new self($user_layouts, $agent_layouts);
	}


	/**
	 * @param LayoutCollection $user_layouts
	 * @param LayoutCollection $agent_layouts
	 */
	public function __construct(LayoutCollection $user_layouts, LayoutCollection $agent_layouts)
	{
		$this->user_layouts  = $user_layouts;
		$this->agent_layouts = $agent_layouts;
	}


	/**
	 * @return LayoutCollection
	 */
	public function getUserLayouts()
	{
		return $this->user_layouts;
	}


	/**
	 * @return LayoutCollection
	 */
	public function getAgentLayouts()
	{
		return $this->agent_layouts;
	}


	/**
	 * @return array
	 */
	public function getUserLayoutItems()
	{
		return $this->_collectLayoutItems($this->user_layouts);
	}


	/**
	 * @return array
	 */
	public function getAgentLayoutItems()
	{
		return $this->_collectLayoutItems($this->user_layouts);
	}


	/**
	 * @param LayoutCollection $coll
	 * @return array
	 */
	private function _collectLayoutItems(LayoutCollection $coll)
	{
		$items = array();

		foreach ($coll as $layout) {
			foreach ($layout as $item) {
				if (!isset($items[$item->getId()])) {
					$items[$item->getId()] = array(
						'id'           => $item->getId(),
						'field_type'   => $item->getFieldType(),
						'field_id'     => $item->getFieldId(),
						'has_criteria' => false,
					);
				}

				if ($item->hasCriteria()) {
					$items[$item->getId()]['has_criteria'] = true;
				}
			}
		}

		return $items;
	}
}