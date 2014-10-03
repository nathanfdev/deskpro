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
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Collection\LazyCollection;
use Orb\Util\Arrays;

class TicketPriorities extends LazyCollection
{
	/**
	 * @var int
	 */
	private $default_id;


	/**
	 * @return array
	 */
	protected function loadRecords()
	{
		$recs = $this->em->getRepository('DeskPRO:TicketPriority')->getAll();
		$recs = Arrays::keyFromData($recs, 'id');
		return $recs;
	}


	/**
	 * This sets the 'default' preference.
	 * Note that setting an invalid or bogus ID here will not result in an exception.
	 *
	 * With an invalid pref, getDefaultPriority() will return the first selectable option.
	 * Use getById() to check if a exists before calling this.
	 *
	 * @param int $obj_or_id
	 */
	public function setDefaultPriorityPreference($obj_or_id)
	{
		if ($obj_or_id === null) {
			$this->default_id = null;
		} else if (is_object($obj_or_id)) {
			$this->default_id = $obj_or_id->id;
		} else {
			$this->default_id = intval($obj_or_id);
		}
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketWorkflow
	 */
	public function getDefaultPriority()
	{
		if (!$this->default_id || !$this->getById($this->default_id)) {
			foreach ($this->getAll() as $dep) {
				$this->default_id = $dep->getId();
				break;
			}
		}

		return $this->getById($this->default_id);
	}


	####################################################################################################################
	// implementing these just for better auto-complete in the IDE (due to @return) :-)

	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\TicketWorkflow[]
	 */
	public function getById($id)
	{
		return parent::getById($id);
	}


	/**
	 * @param array $ids
	 * @param bool $keyed
	 * @return \Application\DeskPRO\Entity\TicketWorkflow[]
	 */
	public function getByIds(array $ids, $keyed = false)
	{
		return parent::getByIds($ids, $keyed);
	}


	/**
	 * @return \Application\DeskPRO\Entity\TicketWorkflow[]
	 */
	public function getAll()
	{
		return parent::getAll();
	}
}