<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\InstallBundle\Upgrade\Build;

class Build1410361094 extends AbstractBuild
{
	public function run()
	{
		$this->out("Correct status on tickets where user has validated themselves");
		$this->execMutateSql("
			UPDATE tickets
				LEFT JOIN people ON (people.id = tickets.person_id)
				LEFT JOIN people_emails ON (people_emails.id = people.primary_email_id)
			SET tickets.status = 'awaiting_agent', tickets.hidden_status = NULL
			WHERE
				tickets.status = 'hidden'
				AND tickets.hidden_status = 'validating'
				AND tickets.person_email_id IS NULL
				AND people_emails.is_validated = 1
		");

		$this->out("Refill search tables");
		$this->container->getEm()->getRepository('DeskPRO:Ticket')->fillSearchTable();
	}
}