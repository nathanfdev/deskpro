<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

class People
{
	/**
	 * Get an array of tickets from the passed IDs.
	 *
	 * @param array $ids
	 * @return array
	 */
	public function getNotesForPerson(array $ids)
	{
		return App::getOrm()
			->getRepository('DeskPRO:Ticket')
			->getTicketsFromIds($ids);
	}


	public function getPeopleOptions()
	{
		$options = array();

		$options['organizations'] = App::getEntityRepository('DeskPRO:Organization')->getOrganizationNames();
		$options['usergroups'] = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();
		$options['languages'] = App::getEntityRepository('DeskPRO:Language')->getTitles();

		return $options;
	}
}
