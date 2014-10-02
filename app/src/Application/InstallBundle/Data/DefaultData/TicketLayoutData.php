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
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;

class TicketLayoutData extends AbstractDefaultData
{
	public function runInstall()
	{
		$exists = $this->getDb()->fetchColumn("SELECT id FROM ticket_layouts WHERE department_id IS NULL");

		if (!$exists) {
			$ticket_layout               = new TicketLayout();
			$ticket_layout->is_enabled   = true;
			$ticket_layout->user_layout  = new Layout();
			$ticket_layout->agent_layout = new Layout();

			foreach (array('department', 'subject', 'message') as $field) {
				$ticket_layout->user_layout->add(new LayoutField($field));
				$ticket_layout->agent_layout->add(new LayoutField($field));
			}

			$ticket_layout->user_layout->add(new LayoutField('attach'));

			$this->getEm()->persist($ticket_layout);
			$this->getEm()->flush();
		}
	}

	public function runReset()
	{
		$exists = $this->getDb()->fetchColumn("SELECT id FROM ticket_layouts WHERE department_id IS NULL");
		if ($exists) {
			$this->getDb()->delete('ticket_layouts', array('id' => $exists));
		}
		$this->runInstall();
	}

	public function runSync()
	{
		$exists = $this->getDb()->fetchColumn("SELECT id FROM ticket_layouts WHERE department_id IS NULL");
		if (!$exists) {
			$this->runInstall();
		}
	}
}