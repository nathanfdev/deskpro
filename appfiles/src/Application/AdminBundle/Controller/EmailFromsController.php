<?php

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\AdminBundle\Form\EditEmailFromType;
use \Orb\Util\Arrays;
use \Symfony\Component\Form;

class EmailFromsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of email addresses
	 */
	public function listAction()
	{
		$this->rememberLastPage();

		$all_email_froms = $this->em->createQuery("
			SELECT e
			FROM DeskPRO:EmailFrom e
			ORDER BY e.name ASC
		")->getResult();

		return $this->render('AdminBundle:EmailFroms:list.html.twig', array(
			'all_email_froms' => $all_email_froms
		));
	}

	############################################################################
	# new
	############################################################################

	/**
	 * Show the type selector
	 */
	public function newAction()
	{
		return $this->render('AdminBundle:EmailFroms:new.html.twig', array(

		));
	}

	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a gateway
	 */
	public function editAction($email_id)
	{
		if (!$email_id) {
			$email_from = new Entity\EmailFrom();
			$email_from['transport_options'] = array('type' => $this->in->getString('type'));
		} else {
			$email_from = App::getEntityRepository('DeskPRO:EmailFrom')->find($email_id);
		}

		$email_from['transport_class'] = 'Application\\DeskPRO\\Entity\\EmailFrom::createTransportInstance';

		$form = $this->get('form.factory')->create(new EditEmailFromType($email_from));

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($email_from);
				App::getOrm()->flush();

				$row_html = $this->renderView('AdminBundle:EmailFroms:list-row.html.twig', array('email_from' => $email_from));
			}
		}

		return $this->render('AdminBundle:EmailFroms:edit.html.twig', array(
			'form'        => $form->createView(),
			'email_from'  => $email_from,
			'is_edited'   => $is_edited,
			'row_html'    => $row_html
		));
	}
}