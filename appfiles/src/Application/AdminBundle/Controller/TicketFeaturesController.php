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
 * Misc
 */
class TicketFeaturesController extends AbstractController
{
	############################################################################
	# index
	############################################################################

	public function indexAction()
	{
 		return $this->render('AdminBundle:TicketFeatures:index.html.twig', array(

		));
	}
}
