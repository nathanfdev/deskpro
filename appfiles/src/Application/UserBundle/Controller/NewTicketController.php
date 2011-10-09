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

use Application\UserBundle\Form\NewTicketType;

class NewTicketController extends AbstractController
{
	################################################################################
	# new-ticket
	################################################################################

	/**
	 * Create a new ticket
	 */
    public function newAction()
    {
		$newticket = new \Application\DeskPRO\Tickets\NewTicket\NewTicket(
			Entity\Ticket::CREATED_WEB_PERSON,
			$this->person
		);
		$newticket->setPersonContext($this->person);

		if ($this->search_query && !$this->request->isPost()) {
			$newticket->ticket->subject = $this->search_query;
		}

		$newticket_formtype = new NewTicketType($this->person);
		$form = $this->get('form.factory')->create($newticket_formtype, $newticket);

		$captcha = $this->container->getSystemObject('form_captcha', array('type' => 'user_newticket'));

		$errors = array();
		$error_fields = array();

		$validator = new \Application\UserBundle\Validator\NewTicketValidator();

		// Custom fields
		$ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
		$ticket_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(array(), $ticket_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'newticket[custom_ticket_fields]');
		$custom_fields = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs, $ticket_data_structured, $custom_fields_form);

		$ticket_display = new \Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection('user');
		$ticket_display->addPagesFromDb();
		$ticket_display_js = "window.DESKPRO_TICKET_DISPLAY = " . $ticket_display->compileJs() . ";";

		$cat_parents = App::getEntityRepository('DeskPRO:TicketCategory')->getCategoryHelper()->getParentMap();
		$ticket_display_js .= 'window.DESKPRO_TICKET_CAT_PARENTS = ' . json_encode($cat_parents) . ';';

		$departments = App::getEntityRepository('DeskPRO:Department')->findAll();
		$ticket_categories = App::getEntityRepository('DeskPRO:TicketCategory')->findAll();

		$captcha_html = '';
		if ($captcha) {
			$captcha_html = $captcha->getHtml();
		}

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			$newticket->ticket->attach_ids = $this->in->getCleanValueArray('attach_ids', 'string', 'discard');
			$newticket->ticket->attach_ids_authed = true;

			if ($validator->isValid($newticket)) {

				$ticket = $newticket->save();
				$person = $ticket['person'];

				// Its no longer a preticket, so we can delete the record
				if ($preticket_id = $this->in->getUint('preticket_status_id')) {
					$preticket = App::findEntity('DeskPRO:PreticketContent', $id);

					// Must be same user
					if ($preticket) {
						if (!$preticket->visitor || $preticket->visitor->getId() != $this->session->getVisitor()->getId()) {
							$preticket = null;
						}
					}

					if ($preticket) {
						$this->em->remove($preticket);
						$this->em->flush();
					}
				}

				if ($ticket->person_email_validating) {
					$this->session->setFlash('new_ticket_validating', $ticket->person_email_validating->getEmail());
				}

				// New users are always sent back to home with flash message.
				if ($person->isNewPerson()) {
					$go = 'front';

				// Existing users are redirected to the ticket if they're using a validated email address.
				// Otherwise they're sent back to the homepage just like an unregistered user is
				} else {
					if ($ticket->person_email_validating || $ticket->person->id != $this->person->id) {
						$go = 'front';
					} else {
						$go = 'ticket';
					}
				}

				if ($go == 'front') {
					if ($ticket->person_email_validating) {
						$this->session->setFlash('new_ticket_validating_email', $ticket->person_email_validating->getEmail());
					} else {
						$this->session->setFlash('new_ticket_email', $ticket->person_email->getEmail());
					}
					$this->session->setFlash('new_ticket', $ticket->getPublicId());

					return $this->redirectRoute('user');
				} else {
					return $this->redirectRoute('user_tickets_view', array('ticket_ref' => $ticket['public_id']));
				}
			} else {
				$errors = $validator->getErrors(true);
				$error_fields = $validator->getErrorGroups(true);
			}
		}

		return $this->render('UserBundle:NewTicket:new-ticket.html.twig', array(
			'departments' => $departments,
			'ticket_categories' => $ticket_categories,

			'newticket' => $newticket,
			'ticket_options' => $newticket_formtype->getTicketOptions(),
			'newticket_formtype' => $newticket_formtype,
			'custom_fields' => $newticket_formtype->getTicketFields(),
			'form' => $form->createView(),
			'custom_fields' => $custom_fields,
			'ticket_display_js' => $ticket_display_js,

			'captcha_html' => $captcha_html,
			'errors' => $errors,
			'error_fields' => $error_fields,
		));
    }

	/**
	 * Saves a users form in the database incase they abandon the form
	 */
	public function saveStatusAction()
	{
		$id = $this->in->getUint('preticket_status_id');

		$preticket = null;
		if ($id) {
			$preticket = App::findEntity('DeskPRO:PreticketContent', $id);

			// Must be same user
			if ($preticket) {
				if (!$preticket->visitor || $preticket->visitor->getId() != $this->session->getVisitor()->getId()) {
					$preticket = null;
				}
			}
		}

		if (!$preticket) {
			$preticket = Entity\PreticketContent::newForPerson($this->person, true);
		}

		$form_data = $_POST;
		unset($form_data['preticket_status_id']);

		if (!empty($form_data['newticket']['ticket']['subject'])) {
			$preticket->subject = $form_data['newticket']['ticket']['subject'];
		}
		if (!empty($form_data['newticket']['ticket']['message'])) {
			$preticket->message = $form_data['newticket']['ticket']['message'];
		}
		if (!empty($form_data['newticket']['ticket']['department_id'])) {
			$preticket->department_id = $form_data['newticket']['ticket']['department_id'];
		}
		if (!empty($form_data['newticket']['person']['email'])) {
			$preticket->email = $form_data['newticket']['person']['email'];
		}
		if (!empty($form_data['newticket']['person']['name'])) {
			$preticket->name = $form_data['newticket']['person']['name'];
		}

		$preticket->data = $form_data;

		$this->em->beginTransaction();
		$this->em->persist($preticket);
		$this->em->flush();
		$this->em->commit();

		$this->session->set('preticket_id', $preticket->getId());

		return $this->createJsonResponse(array(
			'preticket_status_id' => $preticket->id
		));
	}

	public function contentSolvedRedirectAction()
	{
		$id = $this->in->getUint('preticket_status_id');

		$preticket = null;
		if ($id) {
			$preticket = App::findEntity('DeskPRO:PreticketContent', $id);

			// Must be same user
			if ($preticket) {
				if (!$preticket->visitor || $preticket->visitor->getId() != $this->session->getVisitor()->getId()) {
					$preticket = null;
				}
			}
		}

		$url = $this->in->getString('url');
		if (!$url) {
			$url = $this->get('router')->generate('user');
		}

		$content_type = $this->in->getString('content_type');
		$content_id   = $this->in->getString('content_id');

		// Invalid preticket or content
		if (!$preticket || !$content_type || !$content_id) {
			return $this->redirect($url);
		}

		$preticket->is_solved    = true;
		$preticket->object_type = $content_type;
		$preticket->object_id   = $content_id;

		$this->em->beginTransaction();
		$this->em->persist($preticket);
		$this->em->flush();
		$this->em->commit();

		$this->session->remove('preticket_id');

		return $this->redirect($url);
	}

	public function contentSolvedSaveAction()
	{
		$id = $this->in->getUint('preticket_status_id');

		$preticket = null;
		if ($id) {
			$preticket = App::findEntity('DeskPRO:PreticketContent', $id);

			// Must be same user
			if ($preticket) {
				if (!$preticket->visitor || $preticket->visitor->getId() != $this->session->getVisitor()->getId()) {
					$preticket = null;
				}
			}
		}

		$content_type = $this->in->getString('content_type');
		$content_id   = $this->in->getString('content_id');

		// Invalid preticket or content
		if (!$preticket || !$content_type || !$content_id) {
			return $this->createJsonResponse(array('invalid_details' => 1));
		}

		if ($this->in->getBool('add_unsolved')) {
			$unsolved = $preticket->unsolved_content;
			$unsolved[] = array($content_type, $content_id);

			$preticket->unsolved_content = $unsolved;
		} else {
			$preticket->is_solved    = true;
			$preticket->object_type = $content_type;
			$preticket->object_id   = $content_id;
		}

		$this->em->beginTransaction();
		$this->em->persist($preticket);
		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array('success' => 1));
	}

	################################################################################
	# thanks
	################################################################################

	public function thanksAction($ticket_ref)
	{
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->findOneByRef($ticket_ref);

		// Must exist, and match the ref n the session (so theres no info leak)
		if (!$ticket OR $ticket['ref'] != $this->session->get('submitted_ticket')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		return $this->render('UserBundle:NewTicket:thanks.html.twig', array(
			'ticket' => $ticket
		));
	}
}
