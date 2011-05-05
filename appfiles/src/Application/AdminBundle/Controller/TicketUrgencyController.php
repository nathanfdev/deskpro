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

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Application\AdminBundle\Urgency\UrgencyOptions;
use Application\AdminBundle\Form\TicketUrgencyOptionsType;

class TicketUrgencyController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$this->rememberLastPage();

		$urgency_options = UrgencyOptions::newFromSystemTriggers();
		$urgency_form = $this->get('form.factory')->create(new TicketUrgencyOptionsType(), $urgency_options);

		return $this->render('AdminBundle:TicketUrgency:list.html.twig', array(
			'urgency_options' => $urgency_options,
			'urgency_options_form' => $urgency_form->createView(),
		));
	}

	############################################################################
	# save-urgency-options
	############################################################################

	/**
	 * Called via ajax to save urgency options
	 */
	public function saveOptionsAction()
	{
		$urgency_options = UrgencyOptions::newFromSystemTriggers();
		$urgency_form = $this->get('form.factory')->create(new TicketUrgencyOptionsType(), $urgency_options);

		if ($this->get('request')->getMethod() == 'POST') {
			$urgency_form->bindRequest($this->get('request'));

			if ($urgency_form->isValid()) {
				$urgency_options->save();
			}
		}

		return $this->createJsonResponse(array('success' => true));
	}
}