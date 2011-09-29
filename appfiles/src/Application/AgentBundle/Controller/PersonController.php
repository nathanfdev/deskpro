<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Orb\Util\Arrays;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonNote;
use Application\DeskPRO\Entity\Organization;

use Application\DeskPRO\App;

/**
 * Handles viewing and editing a person
 */
class PersonController extends AbstractController
{
	############################################################################
	# /agent/people/:person_id                                   agent_people_view
	############################################################################

	public function viewTipAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		return $this->render('AgentBundle:Person:view-tip.html.twig', array(
			'person' => $person,
		));
	}

	public function viewAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		if (!$person['first_name'] && !$person['last_name'] && $person['name']) {
			$parts = explode(' ', $person['name'], 2);
			$parts = Arrays::removeFalsey($parts);

			if ($parts) {
				$person['first_name'] = $parts[0];
				if (isset($parts[1])) {
					$person['last_name'] = $parts[1];
				}
			}
		}

		#------------------------------
		# Custom fields
		#------------------------------

		// Custom fields
		$user_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$user_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($person['custom_data'], $user_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($user_field_defs, $user_data_structured, $custom_fields_form);

		#------------------------------
		# Misc info needed
		#------------------------------

		$notes = App::getEntityRepository('DeskPRO:PersonNote')->getNotesForPerson($person);
		$person_tickets = App::getEntityRepository('DeskPRO:Ticket')->getPersonTickets($person, 5);
		$person_tickets_count = App::getEntityRepository('DeskPRO:Ticket')->countTicketsForPerson($person);

		$activity_stream = $this->em->getRepository('DeskPRO:PersonActivity')->getForPerson($person, 10);

		$contact_data = array();
		foreach ($person->contact_data as $cd) {
			if (!isset($contact_data[$cd->contact_type])) {
				$contact_data[$cd->contact_type] = array();
			}
			$contact_data[$cd->contact_type][] = $cd->getTemplateVars();
		}

		$session = App::getEntityRepository('DeskPRO:Session')->getSessionForPerson($person);

		$timezone_options = \DateTimeZone::listIdentifiers();
		$usergroup_names = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();

		$person->loadHelper('PermissionsManager');
		$person_usergroups_ids = $person->getPermissionsManager()->getUsergroupIds();
		$person_org_usergroups_ids = $person->getPermissionsManager()->getOrganizationUsergroupIds();

		// Org stuff
		$org_members_count = null;
		$org_contact_data = null;
		if ($person->organization) {
			$org_members_count = App::getEntityRepository('DeskPRO:Organization')->countMembersFor($person->organization);

			$org_contact_data = array();
			foreach ($person->organization->contact_data as $cd) {
				if (!isset($contact_data[$cd->contact_type])) {
					$contact_data[$cd->contact_type] = array();
				}
				$org_contact_data[$cd->contact_type][] = $cd->getTemplateVars();
			}
		}

		return $this->render('AgentBundle:Person:view.html.twig', array(
			'person' => $person,
			'person_usergroups_ids' => $person_usergroups_ids,
			'person_org_usergroups_ids' => $person_org_usergroups_ids,
			'session' => $session,
			'timezone_options' => $timezone_options,
			'usergroup_names' => $usergroup_names,
			'contact_data' => $contact_data,
			'activity_stream' => $activity_stream,
			'custom_fields' => $custom_fields,
			'notes' => $notes,
			'person_tickets' => $person_tickets,
			'person_tickets_count' => $person_tickets_count,
			'org_members_count' => $org_members_count,
			'org_contact_data' => $org_contact_data,
		));
	}

	############################################################################
	# viewSession
	############################################################################

	public function viewSessionAction($session_id)
	{
		$session = App::findEntity('DeskPRO:Session', $session_id);

		return $this->render('AgentBundle:Person:session-info.html.twig', array(
			'session' => $session
		));
	}

	############################################################################
	# /agent/people/:person_id/ajax-get-notes           agent_people_ajaxget_notes
	############################################################################

	public function ajaxGetNotesAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$per_page = min($this->in->getUint('pp'), 20);
		$page = $this->in->getUint('p');
		if (!$page) {
			$page = 1;
		}

		$start = ($page - 1) * $per_page;

		$em = App::getOrm();

		$notes = $em->createQuery("
			SELECT n, a
			FROM DeskPRO:PersonNote n
			LEFT JOIN n.agent a
			WHERE n.person_id = ?1
			ORDER BY n.id DESC
		")->setParameter(1, $person['id'])
			->setMaxResults($per_page)
			->setFirstResult($start)
			->execute();

		$html = array();

		foreach ($notes as $note) {
			$html[] = $this->renderView('AgentBundle:Person:note-li.html.twig', array('note' => $note));
		}

		$html = implode('', $html);

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'notes_html' => $html,
			'page' => $page
		));
	}



	############################################################################
	# /agent/people/:person_id/ajax-save                     agent_people_ajaxsave
	############################################################################

	public function ajaxSaveAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		$this->em->beginTransaction();
		$data = array(
			'success' => true
		);

		switch ($this->in->getString('action')) {
			case 'name':
				if ($this->in->getString('name')) {
					$person->name = $this->in->getString('name');
					$this->em->persist($person);
				}
				break;

			case 'timezone':
				if (in_array($this->in->getString('timezone'), \DateTimeZone::listIdentifiers())) {
					$person->timezone = $this->in->getString('timezone');
					$this->em->persist($person);
				}

				$data['bit_html'] = $this->renderView('AgentBundle:Person:view-bit-timezoneinfo.html.twig', array('person' => $person));

				break;

			case 'is_autoresponder':
				$person->is_autoresponder = $this->in->getBool('is_autoresponder');
				$this->em->persist($person);
				break;

			case 'set-primary-email':
				$email_id = $this->in->getUint('email_id');
				if (isset($person->emails[$email_id])) {
					$person->primary_email = $person->emails[$email_id];
					$this->em->persist($person);
				}
				break;

			case 'delete-picture':
				$person->setPictureBlob(null);
				$this->em->persist($person);
				break;

			case 'set-picture':
				$blob = App::findEntity('DeskPRO:Blob', $this->in->getUint('blob_id'));
				if ($blob) {
					$person->setPictureBlob($blob);
					$this->em->persist($person);
				}
				break;

			case 'set-organization':

				$name = $this->in->getString('name');
				if (!$name) {
					$data['organization_id'] = 0;
				} else {
					$org = App::getEntityRepository('DeskPRO:Organization')->getByName($name);

					if (!$org) {
						$org = new Organization();
						$org->name = $name;

						$this->em->persist($org);
						$this->em->flush();
					}

					$person->organization = $org;
					$person->organization_position = $this->in->getString('position');

					$this->em->persist($person);

					// Org stuff
					$org_members_count = null;
					$org_contact_data = null;
					if ($person->organization) {
						$org_members_count = App::getEntityRepository('DeskPRO:Organization')->countMembersFor($person->organization);

						$org_contact_data = array();
						foreach ($person->organization->contact_data as $cd) {
							if (!isset($contact_data[$cd->contact_type])) {
								$contact_data[$cd->contact_type] = array();
							}
							$org_contact_data[$cd->contact_type][] = $cd->getTemplateVars();
						}
					}

					// Regenerate the HTML block
					$html = $this->renderView('AgentBundle:Person:view-org-info.html.twig', array(
						'person' => $person,
						'org_members_count' => $org_members_count,
						'org_contact_data' => $org_contact_data,
					));

					$data['organization_id'] = $org->id;
					$data['html'] = $html;
				}
				break;

			case 'password':
				if ($this->in->getString('password')) {
					$person->password = $this->in->getString('password');
					$this->em->persist($person);

					if ($this->in->getBool('send_email')) {
						$email_body = App::get('templating')->render('DeskPRO:emails_user:agent-changed-password.html.twig', array(
							'person' => $person
						));

						$message = App::getMailer()->createMessage();
						$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
						$message->setSubject('New Password');
						$message->setBody($email_body, 'text/html');
						$message->enableQueueHint();
						App::getMailer()->send($message);
					}
				}
				break;

			default:
				return $this->createJsonResponse(array('error' => true, 'message' => 'Unknown action'));
				break;
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse($data);
	}

	public function ajaxSaveCustomFieldsAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		$user_field_defs = App::getApi('custom_fields.people')->getEnabledFields();

		if (!empty($_POST['custom_fields'])) {
			foreach ($user_field_defs as $field_def) {
				foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
					$person->setCustomData($info[0], $info[1], $info[2]);
				}
			}

			App::getOrm()->persist($person);
			App::getOrm()->flush();
		}

		// Custom fields
		$user_data_structured = App::getApi('custom_fields.util')->createDataHierarchy($person['custom_data'], $user_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'custom_fields');
		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($user_field_defs, $user_data_structured, $custom_fields_form);

		// Usergroups
		$db = App::getDb();
		$db->delete('person2usergroups', array('person_id' => $person['id']));

		$usergroups_ids = $this->in->getCleanValueArray('usergroups', 'uint', 'discard');
		foreach ($usergroups_ids as $u) {
			$db->insert('person2usergroups', array(
				'person_id' => $person['id'],
				'usergroup_id' => $u
			));
		}

		$usergroup_names = App::getEntityRepository('DeskPRO:Usergroup')->getUsergroupNames();

		$person->loadHelper('PermissionsManager');
		$person_org_usergroups_ids = $person->getPermissionsManager()->getOrganizationUsergroupIds();

		return $this->render('AgentBundle:Person:view-customfields-rendered-rows.html.twig', array(
			'person' => $person,
			'custom_fields' => $custom_fields,
			'person_usergroups_ids' => $usergroups_ids,
			'person_org_usergroups_ids' => $person_org_usergroups_ids,
		));
	}

	public function changePictureOverlayAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		return $this->render('AgentBundle:Person:change-person-picture.html.twig', array(
			'person' => $person
		));
	}

	############################################################################
	# save-contact-data
	############################################################################

	public function saveContactDataAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		$this->em->beginTransaction();

		// Editing emails
		foreach ($this->in->getCleanValueArray('emails', 'string', 'uint') as $email_id => $email) {
			if (isset($person->emails[$email_id]) AND $person->emails[$email_id]->email != $email) {
				if (!$email) {
					$this->em->remove($person->emails[$email_id]);
					$person->emails->remove($email_id);
				} else {
					$person->emails[$email_id]->email = $email;
					$this->em->persist($person->emails[$email_id]);
				}
			}
		}

		// Adding emails
		foreach ($this->in->getCleanValueArray('new_emails', 'string', 'discard') as $email) {
			$email_rec = $person->addEmailAddressString($email);
			$this->em->persist($email_rec);
		}

		// Removing emails
		foreach ($this->in->getCleanValueArray('remove_emails', 'uint') as $email_id) {
			if (isset($person->emails[$email_id])) {
				$person->emails->remove($email_id);
				$this->em->remove($person->emails[$email_id]);
			}
		}

		// Adding contact data
		foreach ($this->in->getCleanValueArray('new_contact_data') as $type => $inputs) {
			foreach ($inputs as $input) {
				try {
					$contact_data = new PersonContactData();
					$contact_data->contact_type = $type;
					$contact_data->applyFormData($input);

					$contact_data->person = $person;

					$this->em->persist($contact_data);
					$person->contact_data->add($contact_data);
				} catch (\Exception $e) {
					throw $e;
				}
			}
		}

		// Editing values
		foreach ($this->in->getCleanValueArray('new_contact_data') as $id => $input) {
			if (!isset($person->contact_data[$id])) {
				continue;
			}

			$person->contact_data[$id]->applyFormData($input);
			$this->em->persist($person->contact_data[$id]);
		}

		// Removing values
		foreach ($this->in->getCleanValueArray('remove_contact_data', 'uint') as $id) {
			if (isset($person->contact_data[$id])) {
				$this->em->remove($person->contact_data[$id]);
				$person->contact_data->remove($id);
			}
		}

		$this->em->flush();
		$this->em->commit();

		$contact_data = array();
		foreach ($person->contact_data as $cd) {
			if (!isset($contact_data[$cd->contact_type])) {
				$contact_data[$cd->contact_type] = array();
			}
			$contact_data[$cd->contact_type][] = $cd->getTemplateVars();
		}

		$display_html = $this->renderView('AgentBundle:Person:view-contact-display.html.twig', array(
			'person' => $person,
			'contact_data' => $contact_data,
		));
		$editor_overlay_html = $this->renderView('AgentBundle:Person:contact-overlay.html.twig', array(
			'person' => $person,
			'contact_data' => $contact_data,
		));

		return $this->createJsonResponse(array(
			'success' => 1,
			'display_html' => $display_html,
			'editor_overlay_html' => $editor_overlay_html
		));
	}


	############################################################################
	# /agent/people/:person_id/ajax-save-organization        agent_people_ajaxsave_organization
	############################################################################

	public function ajaxSaveOrganizationAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$org_id = $this->in->getUint('organization_id');
		if (!$org_id) {
			$person['organization_id'] = 0;
			$person['organization'] = null;
			$person['organization_position'] = '';

			$em = App::getOrm();
			$em->persist($person);
			$em->flush();
			return $this->createJsonResponse(array(
				'success' => true,
				'person_id' => $person['id'],
				'organization_name' => '',
				'organization_position' => '',
			));
		}

		$org = Organization::getRepository()->find($org_id);

		$person['organization'] = $org;
		$person['organization_position'] = $this->in->getString('organization_position');

		$em = App::getOrm();
		$em->persist($person);
		$em->flush();

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'organization_name' => $org['name'],
			'organization_position' => $person['organization_position'],
		));
	}


	############################################################################
	# /agent/people/:person_id/ajax-save-emails       agent_people_ajaxsave_emails
	############################################################################

	// TODO error checking
	public function ajaxSaveEmailsAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$del_ids = $this->in->getCleanValueArray('del_ids', 'uint', 'discard');
		$new_emails = $this->in->getCleanValueArray('new_emails', 'string', 'discard');
		$primary_id = $this->in->getString('primary_id');

		$this->em->beginTransaction();

		if (ctype_digit($primary_id) AND $person['primary_email_id'] != $primary_id) {
			$email = $person->getEmailId($primary_id);
			$person['primary_email'] = $email;
			$person['primary_email_id'] = $primary_id;
		}

		foreach ($del_ids as $id) {
			$person->removeEmailAddressId($id);
		}

		foreach ($new_emails as $email_address) {
			$email = new PersonEmail();
			$email['email'] = $email_address;
			$email['is_validated'] = true;

			$person->addEmailAddress($email);

			if ($primary_id == $email['email']) {
				$person['primary_email'] = $email;
				$primary_id = $email['id'];
			}
		}

		$first_email = null;
		$found = false;
		foreach ($person['emails'] as $email) {
			if (!$first_email) $first_email = $email;
			if ($email['id'] == $primary_id) {
				$found = true;
				break;
			}
		}

		if (!$found) {
			$person['primary_email'] = $first_email;
		}

		$this->em->persist($person);
		$this->em->flush();
		$this->em->commit();

		$emails_list = array();
		foreach ($person['emails'] as $email) {
			$emails_list[] = $email['email'];
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'dlg_html' => $this->renderView('AgentBundle:Person:email-dlg-li.html.twig', array('person' => $person)),
			'emails_list' => $emails_list
		));
	}

	############################################################################
	# /agent/people/:person_id/ajax-save-note           agent_people_ajaxsave_note
	############################################################################

	// TODO error checking
	public function ajaxSaveNoteAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$note_txt = $this->in->getString('note');

		$em = App::getOrm();
		$em->beginTransaction();

		$note = new PersonNote();
		$note['agent'] = $this->person;
		$note['person'] = $person;
		$note['note'] = $note_txt;
		$em->persist($note);

		$em->flush();
		$em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'note_li_html' => $this->renderView('AgentBundle:Person:note-li.html.twig', array('note' => $note))
		));
	}

	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$person->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($person);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	############################################################################
	# New person
	############################################################################

	public function newPersonAction()
	{
		$state = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId('agent.ui.state.newperson', $this->person->id);

		#------------------------------
		# Custom fields
		#------------------------------

		// Custom fields
		$user_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$user_data_structured = App::getApi('custom_fields.util')->createDataHierarchy(array(), $user_field_defs);

		// We use this fieldgroup so the form names are part of custom_fields array: custom_fields[field_1] etc
		// So dont remove it even though it looks like it's not used! :-)
		$custom_fields_form = $this->get('form.factory')->createNamedBuilder('form', 'newperson[custom_fields]');
		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($user_field_defs, $user_data_structured, $custom_fields_form);

		$timezone_options = \DateTimeZone::listIdentifiers();

		return $this->render('AgentBundle:Person:newperson.html.twig', array(
			'state' => $state,
			'custom_fields' => $custom_fields,
			'timezone_options' => $timezone_options,
		));
	}

	public function newPersonSaveAction()
	{
		$newperson = new \Application\AgentBundle\Form\Model\NewPerson($this->person);

		$formType = new \Application\AgentBundle\Form\Type\NewPerson();
		$form = $this->get('form.factory')->create($formType, $newperson);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$form->isValid();

			$newperson->setCustomFieldForm($_POST);
			$newperson->save();

			$person = $newperson->getPerson();

			App::getOrm()->getRepository('DeskPRO:PersonPref')->deletePrefForPersonId('agent.ui.state.newperson', $this->person->id);

			return $this->createJsonResponse(array(
				'success' => true,
				'person_id' => $person['id']
			));
		} else {
			return $this->createJsonResponse(array(
				'success' => false,
			));
		}
	}

	/**
	 * @return Application\DeskPRO\Entity\Person
	 */
	protected function getPersonOr404($person_id)
	{
		try {
			$person = $this->em->find('DeskPRO:Person', $person_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no person with ID $person_id");
		}

		return $person;
	}
}
