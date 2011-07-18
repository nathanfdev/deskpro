<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Item\Portal;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PortalPageDisplay;

class Userinfo extends Template
{
	protected function init()
	{
		$this->setOption('tpl', 'UserBundle:Portal:userinfo-' . $this->section . '.html.twig');
	}

	public function getVars()
	{
		$ticket_count = 0;
		if (!$this->person_context->isGuest()) {
			$ticket_count = App::getEntityRepository('DeskPRO:Ticket')->countTicketsForPerson($this->person_context, array('open', 'pending'));
		}

		return array(
			'ticket_count' => $ticket_count,
		);
	}
}