<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditTicketPriorityType;

/**
 * Various email settingso in a single page
 */
class EmailPropertiesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of priorities
	 */
	public function listAction()
	{
		$this->rememberLastPage();

		$sections = array(
			'gateways'    => $this->forward('AdminBundle:EmailGateways:list')->getContent(),
			'froms'       => $this->forward('AdminBundle:EmailFroms:list')->getContent(),
		);
		return $this->render('AdminBundle:EmailProperties:list.html.twig', array(
			'sections' => $sections
		));
	}
}
