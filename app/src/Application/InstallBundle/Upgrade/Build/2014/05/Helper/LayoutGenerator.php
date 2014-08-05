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

namespace Application\InstallBundle\Upgrade\Build\Helper201405;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\TicketLayout as TicketLayoutEntity;
use Application\DeskPRO\TicketLayout;

class LayoutGenerator
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;


	/**
	 * @param DeskproContainer $container
	 */
	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	/**
	 * @return TicketLayoutEntity
	 */
	public function getTicketLayout()
	{
		$ticket_layout = new TicketLayoutEntity();
		$ticket_layout->is_enabled = true;
		$ticket_layout->user_layout = $this->getLayout('user');
		$ticket_layout->agent_layout = $this->getLayout('agent');
		return $ticket_layout;
	}

	private function getLayout($type)
	{
		$layout = new TicketLayout\Layout();
		$layout->add(new TicketLayout\LayoutField('user_name'));
		$layout->add(new TicketLayout\LayoutField('user_email'));
		$layout->add(new TicketLayout\LayoutField('department'));

		if ($this->container->getSetting('core.use_ticket_category')) {
			$layout->add(new TicketLayout\LayoutField('category'));
		}
		if ($this->container->getSetting('core.use_ticket_priority')) {
			$layout->add(new TicketLayout\LayoutField('priority'));
		}
		if ($this->container->getSetting('core.use_ticket_workflow')) {
			$layout->add(new TicketLayout\LayoutField('workflow'));
		}
		if ($this->container->getSetting('core.use_product')) {
			$layout->add(new TicketLayout\LayoutField('product'));
		}

		foreach ($this->container->getTicketFieldManager()->getFields() as $f) {
			if ($type == 'agent' || !$f->is_agent_field) {
				$layout->add(new TicketLayout\LayoutField('ticket_field', $f->id));
			}
		}

		$layout->add(new TicketLayout\LayoutField('subject'));
		$layout->add(new TicketLayout\LayoutField('message'));
		$layout->add(new TicketLayout\LayoutField('attach'));

		return $layout;
	}
}