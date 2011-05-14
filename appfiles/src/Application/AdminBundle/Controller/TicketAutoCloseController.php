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

use Application\AdminBundle\AutoClose\AutoCloseOptions;
use Application\AdminBundle\Form\TicketAutoCloseOptionsType;

class TicketAutoCloseController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$this->rememberLastPage();

		$autoclose_options = AutoCloseOptions::newFromSystemTriggers();
		$autoclose_form = $this->get('form.factory')->create(new TicketAutoCloseOptionsType(), $autoclose_options);

		return $this->render('AdminBundle:TicketAutoClose:list.html.twig', array(
			'autoclose_options' => $autoclose_options,
			'autoclose_options_form' => $autoclose_form->createView(),
		));
	}

	############################################################################
	# save-autoclose-options
	############################################################################

	/**
	 * Called via ajax to save autoclose options
	 */
	public function saveOptionsAction()
	{
		$autoclose_options = AutoCloseOptions::newFromSystemTriggers();
		$autoclose_form = $this->get('form.factory')->create(new TicketAutoCloseOptionsType(), $autoclose_options);

		if ($this->get('request')->getMethod() == 'POST') {
			$autoclose_form->bindRequest($this->get('request'));

			if ($autoclose_form->isValid()) {
				$autoclose_options->save();
			}
		}

		return $this->createJsonResponse(array('success' => true));
	}
}