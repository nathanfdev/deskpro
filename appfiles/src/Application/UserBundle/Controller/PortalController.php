<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @category Controllers
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\PageDisplay\Page\PortalPage;

class PortalController extends AbstractController
{
    public function portalAction()
    {
		// TODO: Hard-coded until we get editor working
		$content_pagedisplay = new PortalPageDisplay();
		$content_pagedisplay['section'] = PortalPageDisplay::SECTION_CONTENT;
		$content_pagedisplay['data'] = array(
			array(
				'type' => 'omni_search',
			),
			array(
				'type' => 'kb'
			),
			array(
				'type' => 'ideas'
			),
		);

		$sidebar_pagedisplay = new PortalPageDisplay();
		$sidebar_pagedisplay['section'] = PortalPageDisplay::SECTION_SIDEBAR;
		$sidebar_pagedisplay['data'] = array(
			array(
				'type' => 'userinfo',
			),
			array(
				'type' => 'contact',
			),
			array(
				'type' => 'nav',
			),
			array(
				'type' => 'kb'
			),
			array(
				'type' => 'ideas',
				'status' => 'active',
				'block_title' => 'Recent Accepted Feedback'
			)
		);

		$portal_page = new PortalPage($this, $this->person);
		$portal_page->addPageDisplay($content_pagedisplay);
		$portal_page->addPageDisplay($sidebar_pagedisplay);

        return $this->render('UserBundle:Portal:portal.html.twig', array(
			'portal_page' => $portal_page,
		));
    }
}