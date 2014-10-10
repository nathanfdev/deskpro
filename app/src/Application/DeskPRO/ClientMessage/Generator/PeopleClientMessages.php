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
 * @category ClientMessage
 */

namespace Application\DeskPRO\ClientMessage\Generator;

use Application\DeskPRO\Entity\ClientMessage;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;

class PeopleClientMessages
{
	public static function createNewPersonMessages(Person $person)
	{
		$cm = new ClientMessage();
		$cm->fromArray(array(
			'channel' => 'agent.person.added',
			'data' => array(
				'person_id'    => $person->id,
				'person_name'  => $person->getDisplayName(),
				'date_created' => $person->date_created->getTimestamp()
			)
		));

		return array($cm);
	}

	public static function createNewOrgMessages(Organization $org)
	{
		$cm = new ClientMessage();
		$cm->fromArray(array(
			'channel' => 'agent.org.added',
			'data' => array(
				'organization_id'    => $org->id,
				'organization_name'  => $org->name,
				'date_created'       => $org->date_created->getTimestamp()
			)
		));

		return array($cm);
	}
}