<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

class TestController extends AbstractController
{
	public function indexAction()
	{
		if (1) {
			$this->session->setFlash('new_ticket_validating_email', 'chroder@gmail.com');
		} else {
			$this->session->setFlash('new_ticket_email', $ticket->person_email->getEmail());
		}
		$this->session->setFlash('new_ticket', 21);

		return $this->redirectRoute('user');

		return $this->render('UserBundle:Test:index.html.twig');
	}
}
