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
        return $this->render('UserBundle:Portal:portal.html.twig', array(
			
		));
    }
}