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

namespace Application\DeskPRO\ORM\StateChange\Ticket;

use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\NonStateTrackingInterface;

class ChangeMerge implements ChangeInterface, NonStateTrackingInterface
{
	/**
	 * @var string
	 */
	private $field_id;

	/**
	 * @var int
	 */
	private $old_ticket_id;

	/**
	 * @var array
	 */
	private $lost_data;

	/**
	 * @param string $field_id
	 * @param int $old_ticket_id
	 * @param array $lost_data
	 */
	public function __construct($field_id, $old_ticket_id, array $lost_data = array())
	{
		$this->field_id      = $field_id;
		$this->old_ticket_id = $old_ticket_id;
		$this->lost_data     = $lost_data;
	}


	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field_id;
	}


	/**
	 * @return array
	 */
	public function getOld()
	{
		return null;
	}


	/**
	 * @return array
	 */
	public function getNew()
	{
		return array(
			'field_id'      => $this->field_id,
			'old_ticket_id' => $this->old_ticket_id,
			'lost_data'     => $this->lost_data
		);
	}


	/**
	 * @return array
	 */
	public function getLostData()
	{
		return $this->lost_data;
	}


	/**
	 * @param array $lost_data
	 */
	public function setLostData(array $lost_data)
	{
		$this->lost_data = $lost_data;
	}


	/**
	 * @return int
	 */
	public function getOldTicketId()
	{
		return $this->old_ticket_id;
	}



	/**
	 * @return bool
	 */
	public function isSame()
	{
		return false;
	}


	/**
	 * @return bool
	 */
	public function isCollection()
	{
		return false;
	}


	/**
	 * @return bool
	 */
	public function isEntity()
	{
		return false;
	}
}