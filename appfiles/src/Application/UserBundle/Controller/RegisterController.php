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

use Application\UserBundle\Form\RegisterType;

class RegisterController extends AbstractController
{
	public function registerAction()
	{
		$reg_formtype = new RegisterType();
		$form = $this->get('form.factory')->create($reg_formtype);

		return $this->render('UserBundle:Register:register.html.twig', array(
			'form' => $form->createView(),
		));
	}
	
	public function finishAction()
	{
		$person = App::getEntityRepository('DeskPRO:Person')->find($this->session->get('finish_register_person', 0));

		// Invalid person if they dont exist or already are registered.
		// just pop the user back to index
		if (!$person OR $person['is_user'] OR !$this->session->get('finish_register_mode')) {
			return $this->redirectRoute('user');
		}

		$vars = array(
			'person' => $person
		);

		$modeinfo = $this->session->get('finish_register_mode');
		switch ($modeinfo['type']) {
			case 'ticket':
				$tpl = 'finish-ticket.html.twig';
				$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($modeinfo['id']);

				$vars['ticket'] = $ticket;
				break;

			case 'ticket_participant':
				$tpl = 'finish-ticket-participant.html.twig';
				$ticket = App::getEntityRepository('DeskPRO:Ticket')->find($modeinfo['ticket_id']);

				$vars['ticket'] = $ticket;
				break;

			default:
				// invalid :o
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
				break;
		}

		if ($this->in->getBool('process')) {
			$password = $this->in->getString('password');
			$password2 = $this->in->getString('password2');

			if ($password == $password2) {
				$person['password'] = $password;
				$person['is_user'] = true;

				App::getOrm()->persist($person);
				App::getOrm()->flush();

				$this->session->set('auth_person_id', $person['id']);

				$after_url = $this->session->get('after_register');

				// Unset some sess vars
				$this->session->remove('after_register');
				$this->session->remove('finish_register_person');
				$this->session->remove('finish_register_mode');

				if ($after_register) {
					return $this->redirect($after_url);
				} else {
					return $this->redirectRoute('user');
				}
			}
		}

		return $this->render('UserBundle:Register:' . $tpl, $vars);
	}
}