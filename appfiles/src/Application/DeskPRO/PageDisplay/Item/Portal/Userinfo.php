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

use Application\DeskPRO\Entity\PortalPageDisplay;

class Userinfo extends Template
{
	protected function init()
	{
		$this->setOption('tpl', 'UserBundle:Portal:userinfo-' . $this->section . '.html.twig');
	}
}