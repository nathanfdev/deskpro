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

class TicketSlas extends LazyCollection
{
	/**
	 * @return array
	 */
	protected function loadRecords()
	{
		$recs = $this->em->getRepository('DeskPRO:Sla')->findAll();
		$recs = Arrays::keyFromData($recs, 'id');
		return $recs;
	}


	####################################################################################################################
	// implementing these just for better auto-complete in the IDE (due to @return) :-)

	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\Sla
	 */
	public function getById($id)
	{
		return parent::getById($id);
	}


	/**
	 * @param array $ids
	 * @param bool $keyed
	 * @return \Application\DeskPRO\Entity\Sla[]
	 */
	public function getByIds(array $ids, $keyed = false)
	{
		return parent::getByIds($ids, $keyed);
	}


	/**
	 * @return \Application\DeskPRO\Entity\Sla[]
	 */
	public function getAll()
	{
		return parent::getAll();
	}
}